<?php

namespace Sunflowerbiz\Base\Model\Message;
class Notification implements \Magento\Framework\Notification\MessageInterface
{
	private $message;
	protected $notifierPool;
	protected $_modulefeed;
	
	public function __construct(
	 \Magento\Framework\Notification\NotifierInterface $notifierPool,
	 \Sunflowerbiz\Base\Model\ModuleFeed $modulefeed
	)
   {
       // Retrieve  message 
	   $this->_modulefeed=$modulefeed;
	   $notifications=$this->_modulefeed->getConfigData('se_sunflowerbiz_base/notifications/active');
	   $this->message=[
			'id'=>'sfbasemessage'.time(),
			'display'=>false,
			'text'=>'',
			'type'=>'SEVERITY_NOTICE'
		   ];
		$showTimes=(int)$this->_modulefeed->getAuthData('SFBASE_AD_SHOW');
		if($notifications  && $showTimes<=1) {// 
		
		   $Xml=$this->_modulefeed->getXML();
		   $feedXml = simplexml_load_string($Xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		   $jsonStr = json_encode($feedXml);
			$jsonArray = json_decode($jsonStr,true);
			if ($feedXml && isset($jsonArray['channel']) &&  isset($jsonArray['channel']['topMessage']) && !is_array($jsonArray['channel']['topMessage']) && $jsonArray['channel']['topMessage']!="") {
		
				$text=(string) html_entity_decode($jsonArray['channel']['topMessage']);
					 $this->message=[
					'id'=>'sfbasemessage'.time(),
					'display'=>true,
					'text'=>($text),
					'type'=>'SEVERITY_NOTICE'
				   ];
				   $showTimes++;
				   $this->_modulefeed->setAuthData('SFBASE_AD_SHOW',$showTimes);
			   
			  }
	   }
	   
	  
   }
  
   public function getIdentity()
   {
       // Retrieve unique message identity
       return $this->message['id'];
   }
   public function isDisplayed()
   {
       // Return true to show your message, false to hide it
       return $this->message['display'];
   }
   public function getText()
   {
       // message text
       return $this->message['text'];
   }
   public function getSeverity()
   {
	   $serverity=$this->message["type"];
       return $serverity;
   }
}