<?php
namespace Sunflowerbiz\Wechat\Controller\Process;
class Notify extends \Magento\Framework\App\Action\Action {
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
		// Fix for Magento2.3 adding isAjax to the request params
		if(interface_exists("\Magento\Framework\App\CsrfAwareActionInterface")) {
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$request = $objectManager->get('\Magento\Framework\App\Request\Http');
			if ( $request->isPost()) {
				$request->setParam('isAjax', true);
				if( empty( $request->getParam('form_key') ) ) {
					$formKey = $objectManager->get(\Magento\Framework\Data\Form\FormKey::class);
					$request->setParam('form_key', $formKey->getFormKey());
				}
			}
		}
		parent::__construct($context);
	}
	/**
     * Return checkout session object
     *
     * @return \Magento\Checkout\Model\Session
     */
	protected function _getCheckoutSession() {
		return $this->_checkoutSession;
	}
	/**
     * Set redirect
     */
	public function execute() {
		date_default_timezone_set('Asia/Shanghai');
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
		$base  =  $directory->getRoot();
		$xml = file_get_contents("php://input");
		
		
		/* 
	 $xml = '<xml>
	<out_trade_no>000000001</out_trade_no>
	<result_code>SUCCESS</result_code>
	<transaction_id>11111111111111111</transaction_id>
	<sign>2FED1B3BD40B566DEC252EA43DAD3BB7</sign>
	</xml>';*/
		$xml_parser = xml_parser_create();
		if(!xml_parse($xml_parser,$xml,true)) {
			xml_parser_free($xml_parser);
			return false;
		}
		$xml2Array = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		$this->_orderHistoryFactory =  $this->_objectManager->get('\Magento\Sales\Model\Order\Status\HistoryFactory');
		$active_log_stock_update = $this->_objectManager->create('Sunflowerbiz\Wechat\Helper\Data')->getConfig('payment/wechatpayment/enable_log');
		$order_status_payment_accepted = $this->_objectManager->create('Sunflowerbiz\Wechat\Helper\Data')->getConfig('payment/wechatpayment/order_status_payment_accepted');
		if($active_log_stock_update) {
			$logdir=$base .'/var/log/';
			if(!file_exists($logdir))mkdir($logdir,0777);
			if( $dumpFile = fopen($base .'/var/log/WechatPay.log', 'a+')) {
				fwrite($dumpFile, date("Y-m-d H:i:s").' : Response data Notify: '.$xml."\r\n");
			}
		}
		//checksign
		$input = $this->_objectManager->create('\Sunflowerbiz\Wechat\Model\Unifiedorder');
		$app_key = $this->_objectManager->create('Sunflowerbiz\Wechat\Helper\Data')->getConfig('payment/wechatpayment/app_key');
		$input->SetAppkey($app_key);
		if(is_array($xml2Array)) {
			foreach ($xml2Array as $k => $v) {
				if($k!='sign')
					$input->SetKV($k,$v);
			}
		}
		$sign=$input->MakeSign(true);
		if(isset($xml2Array['result_code']) && $xml2Array['result_code']=='SUCCESS' && $sign==$xml2Array['sign']) {
			$incrementId = $xml2Array['out_trade_no'];
			$order = $this->_getOrder($incrementId);
			$comment = "Payment Done.";
			$order = $objectManager->create('\Magento\Sales\Model\Order')->loadByIncrementId($incrementId);
			$order->setState($order_status_payment_accepted)->setStatus($order_status_payment_accepted);
			$order->setTotalPaid($order->getGrandTotal());
			$this->getResponse()->setBody("success");
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$core_write = $resource->getConnection();
			$tableName = $resource->getTableName('sun_wechat_history');
			$selectsql= "select * from `".$tableName."` where order_id='".$incrementId."' and status='SUCCESS'";
			$wechat_history=$core_write->fetchAll($selectsql);
			if(count($wechat_history)<=0) {
				$order->save();
				$history = $this->_orderHistoryFactory->create()
				                ->setStatus($order_status_payment_accepted)
				                ->setComment($comment)
				                ->setEntityName('order')
				                ->setOrder($order);
				$history->save();
				$insertsql = "insert into `" . $tableName . "` (create_time,order_id,token_value,status) values (now(),'" . $incrementId . "','" . $xml2Array['transaction_id'] . "','SUCCESS')";
				$core_write->query($insertsql);
				$eventManager= $objectManager->create('\Magento\Framework\Event\Manager');
				$eventData= ['incrementId'=>$incrementId];
				$eventManager->dispatch('sunflowerbiz_wechat_payment_notify_success',['EventData'=>$eventData]);
				$this->createTransaction($order, $xml2Array['transaction_id'], $order->getGrandTotal(), "");
			}
			// $this->_redirect('checkout/onepage/success');
		}
		return;
	}
	public function createTransaction($order, $transactionId, $paymentAmount, $paymentData) {
		try {
			$payment = $order->getPayment();
			$payment->setLastTransId($transactionId);
			$payment->setTransactionId($transactionId);
			$payment->setAdditionalInformation(
			                [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS =>  $paymentData]
			            );
			$formatedPrice = $order->getBaseCurrency()->formatTxt(
			            //$order->getGrandTotal()
			$paymentAmount
			            );
			$message = __('Payment amount is %1.', $formatedPrice);
			$trans = $this->_objectManager->get('\Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface');
			$transaction = $trans->setPayment($payment)
			                ->setOrder($order)
			                ->setTransactionId($transactionId)
			                ->setAdditionalInformation(
			                    [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS =>  $paymentData]
			                )
			                ->setFailSafe(true)
			                ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_PAYMENT);
			$payment->addTransactionCommentsToOrder(
			                $transaction,
			                $message
			            );
			$payment->setParentTransactionId(null);
			$payment->save();
			//            $order->save();
			return  $transaction->save()->getTransactionId();
		}
		catch (Exception $e) {
			$this->logger->error($e->getMessage());
		}
	}
	/**
     * Get order object
     *
     * @return \Magento\Sales\Model\Order
     */
	protected function _getOrder() {
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
	protected function _getCheckout() {
		return $this->_objectManager->get('Magento\Checkout\Model\Session');
	}
	/**
     * @return mixed
     */
	protected function _getQuote() {
		return $this->_objectManager->get('Magento\Quote\Model\Quote');
	}
	/**
     * @return mixed
     */
	protected function _getQuoteManagement() {
		return $this->_objectManager->get('\Magento\Quote\Model\QuoteManagement');
	}
}