<?php


namespace Sunflowerbiz\Base\Block\Adminhtml;

use SimpleXMLElement;

class Messages extends \Magento\Backend\Block\Template
{
   
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;
  	/**
     * @var \Sunflowerbiz\Base\Model\ModuleFeed
     */
    private $_modulefeed;	 
	/**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $_moduleList;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
		\Sunflowerbiz\Base\Model\ModuleFeed $modulefeed,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->request = $request;
		$this->_modulefeed =$modulefeed;
    }

 
    /**
     * @return string
     */
    public function getExtraJs()
    {
		
		$notifications=$this->_scopeConfig->getValue('se_sunflowerbiz_base/notifications/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if(!$notifications) return '';
		$updateNum=0;
		$js="";
		$xml=$this->_modulefeed->getXML();
		$modules = $this->_modulefeed->getModuleList()->getNames();
		$dispatchResult = new \Magento\Framework\DataObject($modules);
        $modules = $dispatchResult->toArray();
        sort($modules);
		$installedmodule=array();
        foreach ($modules as $moduleName) {
            if (strstr($moduleName, 'Sunflowerbiz') === false || $moduleName === 'Sunflowerbiz_Base' ) 
                continue;
            $installedmodule[]=$moduleName;
        }
		$feedXml  = new SimpleXMLElement($xml);
		 if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
				foreach ($feedXml->channel->item as $item) {
					$scode = (string)$item->code;
					$allExtension[$scode]=$item;
			}
		}
		$needUpdate=false;
		foreach($installedmodule as $code){	
					$module=$this->_modulefeed->getModuleInfo($code);
					if (!is_array($module) ||
					   !array_key_exists('version', $module) ||
					   !array_key_exists('description', $module)
					) {
						continue;
					}
							
					$currentVer = $module['version'];
					$hasNewVersion=false;
					
					$item=isset($allExtension[strtolower($code)])?$allExtension[strtolower($code)]:false;
					$link=$extra=$nversion="";
					$title=$code;
					if($item){
						$title=$item->title;
						$link=$item->link;
						$nversion=$item->version;
						$extra=$item->extra;
					}
					if(!empty($nversion) && version_compare($currentVer, $nversion, '<')){
					$hasNewVersion=true;
					$updateNum++;
					$needUpdate=true;
					}
		}	
		
       	$js="jQuery('#menu-sunflowerbiz-base-category-configuration a').prepend('<b>$updateNum</b>');jQuery('.sunflowerbiz-tab .admin__page-nav-title').append('<b>$updateNum</b>');";
        return $js;
    }
}
