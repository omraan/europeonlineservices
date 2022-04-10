<?php

namespace Sunflowerbiz\Wechat\Controller\Process;

class Scanevent extends \Magento\Framework\App\Action\Action 
{

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote = false;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;
    protected $_scopeConfig;
    protected $_orderHistoryFactory;


    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context
    ) {
	
		
        parent::__construct($context);
		
    }

    /**
     * Return checkout session object
     *
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * Set redirect
     */
    public function execute()
    {
	
	    $orderId = $this->getRequest()->getParam('orderId') ;
		if(!is_numeric($orderId))$orderId=0;
        $responses = array('status' => 'no', 'message' => '');
        $CanPay = true;
        if ($orderId) {
			
			
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$core_write = $resource->getConnection();
			$tableName = $resource->getTableName('sun_wechat_history');
			
			$token_value="";
				$selectsql= "select * from `".$tableName."` where order_id='".$orderId."' and status='SUCCESS'";
				$wechat_history=$core_write->fetchAll($selectsql);
				if(count($wechat_history)>0){			
						foreach($wechat_history as $history)
							$token_value=$history['token_value'];			
				}
				
				if($token_value!='')
				 $responses = array('status'=>'ok','message' => __('Order Paied success.') );
				

		}
		$this->getResponse()->setBody(json_encode($responses));

		
		return;
    }

}