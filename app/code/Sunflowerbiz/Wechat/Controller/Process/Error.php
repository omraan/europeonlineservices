<?php

namespace Sunflowerbiz\Wechat\Controller\Process;

class Error extends \Magento\Framework\App\Action\Action 
{

    protected $_quote = false;
    protected $_checkoutSession;

    protected $_order;
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

    protected function _getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * Set redirect
     */
    public function execute()
    {
		 $this->_redirect('checkout/cart');
  
    }

}