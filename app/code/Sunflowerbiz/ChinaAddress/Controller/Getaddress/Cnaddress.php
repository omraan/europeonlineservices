<?php

namespace Sunflowerbiz\ChinaAddress\Controller\Getaddress;

class Cnaddress extends \Magento\Framework\App\Action\Action 
{

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
	$type = $this->getRequest()->getParam('type') ;		
	$name = $this->getRequest()->getParam('name') ;	
	
	
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
	$core_write = $resource->getConnection();
	$tableName = $resource->getTableName('address_cn_data');
	$html="";
	if($type=='cncity'){
	$html="";
	 $selectsql = "select city from `" . $tableName . "` where province='" . $name . "' group by city  ";
	 //order by CONVERT( city USING gbk ) COLLATE gbk_chinese_ci  asc
	  $html .= '<option value="" ' .  '>' . __('- Please select -'). '</option>';
            $exists = $core_write->fetchAll($selectsql);
            foreach ($exists as $cityarr) {
                $html .= '<option value="' . $cityarr['city'] . '" ' .  '>' . $cityarr['city'] . '</option>';
            }
            $html .= '<optgroup label="====================="></optgroup><option value="new" ' . '>' . __('Not found, enter yourself') . '</option>';

	}	
	if($type=='cnregion'){
	 $html="";
	 $selectsql = "select region from `" . $tableName . "` where city='" . $name . "' group by region  ";
	 //order by CONVERT( city USING gbk ) COLLATE gbk_chinese_ci  asc
	  $html .= '<option value="" ' .  '>' . __('- Please select -'). '</option>';
            $exists = $core_write->fetchAll($selectsql);
            foreach ($exists as $cityarr) {
                $html .= '<option value="' . $cityarr['region'] . '" ' . '>' . $cityarr['region'] . '</option>';
            }
            $html .= '<optgroup label="====================="></optgroup><option value="new" ' . '>' .  __('Not found, enter yourself') . '</option>';

	}		

	$this->getResponse()->setBody($html);
	return ;
    }

    /**
     * Get order object
     *
     * @return \Magento\Sales\Model\Order
     */
    protected function _getOrder()
    {
        if (!$this->_order) {
            $incrementId = $this->_getCheckout()->getLastRealOrderId();
            $this->_orderFactory = $this->_objectManager->get('Magento\Sales\Model\OrderFactory');
            $this->_order = $this->_orderFactory->create()->loadByIncrementId($incrementId);
        }
        return $this->_order;
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckout()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Session');
    }

    /**
     * @return mixed
     */
    protected function _getQuote()
    {
        return $this->_objectManager->get('Magento\Quote\Model\Quote');
    }

    /**
     * @return mixed
     */
    protected function _getQuoteManagement()
    {
        return $this->_objectManager->get('\Magento\Quote\Model\QuoteManagement');
    }
}