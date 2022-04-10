<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sunflowerbiz\Wechat\Model;
/**
 * Pay In Store payment method model
 */
class WechatPayment extends \Magento\Payment\Model\Method\AbstractMethod {
	const CODE       = 'wechatpayment';
	/**
     * Payment code
     *
     * @var string
     */
	protected $_code = 'wechatpayment';
	/**
     * Availability option
     *
     * @var bool
     */
	protected $_isOffline = true;
	protected $_isInitializeNeeded = true;
	protected $_canRefund = true;
	protected $_canRefundInvoicePartial = true;
	public function __construct(
	        \Magento\Framework\Model\Context $context,
	        \Magento\Framework\Registry $registry,
	        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
	        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
	        \Magento\Payment\Helper\Data $paymentData,
	        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
	        \Magento\Payment\Model\Method\Logger $logger,
	        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
	        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
	        array $data = []
	    ) {
		parent::__construct(
		            $context,
		            $registry,
		            $extensionFactory,
		            $customAttributeFactory,
		            $paymentData,
		            $scopeConfig,
		            $logger,
		            $resource,
		            $resourceCollection,
		            $data
		        );
	}
	public function ToUrlParams($urlObj) {
		$buff = "";
		foreach ($urlObj as $k => $v) {
			if ($k != "sign") {
				$buff .= $k . "=" . $v . "&";
			}
		}
		$buff = trim($buff, "&");
		return $buff;
	}
	private function __CreateOauthUrlForOpenid($code) {
		$app_id=  $this->_scopeConfig->getValue('payment/wechatpayment/app_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$app_secret=  $this->_scopeConfig->getValue('payment/wechatpayment/app_secret', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$urlObj["appid"] = $app_id;
		$urlObj["secret"] = $app_secret;
		$urlObj["code"] = $code;
		$urlObj["grant_type"] = "authorization_code";
		$bizString = $this->ToUrlParams($urlObj);
		//if($_SERVER['HTTP_HOST']=='127.0.0.1')  return "http://localhost/testwechatjsapi.php?getopenid=1&".$bizString;
		return "https://api.weixin.qq.com/sns/oauth2/access_token?" . $bizString;
	}
	public function validappid() {
		$url = $this->__CreateOauthUrlForOpenid('CODE');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$res = curl_exec($ch);
		curl_close($ch);
		$data = json_decode($res, true);
		$openid ='';
		if(isset($data['errmsg']) && stristr($data['errmsg'],'invalid code'))
		        return true; else
				return false;
	}
	public function isActive($storeId = null) {
		$app_id=  $this->_scopeConfig->getValue('payment/wechatpayment/app_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$merchant_id=  $this->_scopeConfig->getValue('payment/wechatpayment/merchant_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$app_key=  $this->_scopeConfig->getValue('payment/wechatpayment/app_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if($app_id=="" || $merchant_id=="" || $app_key=="")
				return false;
		//if($this->validappid())
		return (bool)(int)$this->getConfigData('active', $storeId);
		//else
		//return false;
	}
	public function postXmlCurl($xml, $url, $useCert = false, $second = 30,$sslpath="") {
		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		
		if($sslpath!=""){
		curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLCERT, $sslpath);
		curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLKEY,$sslpath);
		}
		//运行curl
		$data = curl_exec($ch);
		//返回结果
		if ($data) {
			curl_close($ch);
			return $data;
		} else {
			$error = curl_errno($ch);
			curl_close($ch);
			throw new \Magento\Framework\Validator\Exception(__('Payment error.' ."curl error!" . $error));
		}
	}
	public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount) {
		/*
			注意：
		1、交易时间超过一年的订单无法提交退款
		2、微信支付退款支持单笔交易分多次退款，多次退款需要提交原支付订单的商户订单号和设置不同的退款单号。申请退款总金额不能超过订单金额。 一笔退款失败后重新提交，请不要更换退款单号，请使用原商户退款单号
		3、请求频率限制：150qps，即每秒钟正常的申请退款请求次数不超过150次
		错误或无效请求频率限制：6qps，即每秒钟异常或错误的退款申请请求不超过6次
		4、每个支付订单的部分退款次数不能超过50次
		*/
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
		$basepath  =  $directory->getRoot();
		$_order = $payment->getOrder();
		;
		$orderId = $_order->getIncrementId();
		$message=$objectManager->create('\Magento\Framework\Message\ManagerInterface');
		$_scopeConfig= $this->_scopeConfig;
		$app_id=  $this->_scopeConfig->getValue('payment/wechatpayment/app_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$merchant_id=  $this->_scopeConfig->getValue('payment/wechatpayment/merchant_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$app_key=  $this->_scopeConfig->getValue('payment/wechatpayment/app_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$enable_log =$this->_scopeConfig->getValue('payment/wechatpayment/enable_log', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$sslpath =$this->_scopeConfig->getValue('payment/wechatpayment/sslpath', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$refund =$this->_scopeConfig->getValue('payment/wechatpayment/refund', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if($orderId!="" && $refund ) {
			$orderStatus=$_order->getStatus();
			//check if refuned
			//do refund
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$core_write = $resource->getConnection();
			$tableName = $resource->getTableName('sun_wechat_history');
			$selectsql = "select token_value from `" . $tableName . "` where order_id='" . $orderId . "' and status='SUCCESS'";
			$rows = $core_write->fetchAll($selectsql);
			$transactionId="";
			foreach($rows as $row)
										$transactionId=$row['token_value'];
			$selectsql = "select * from `" . $tableName . "` where order_id='" . $orderId . "' and status='REFUND'";
			$history = $core_write->fetchAll($selectsql);
			if ( $transactionId!="") {
				//$orderFactory=$objectManager->create('\Magento\Sales\Model\OrderFactory');
				//$_order= $orderFactory->create()->loadByIncrementId($orderId);
				$input =$objectManager->create('\Sunflowerbiz\Wechat\Model\Unifiedorder');
				$ordertotal=round($_order->getGrandTotal(),2);
				$orderCurrencyCode = $_order->getOrderCurrencyCode();
				/*$input->SetKV('error',false);
							$input->SetKV('enable_log',$enable_log);
							$input->SetKV('basepath',$basepath);*/
				$input->SetKV('transaction_id',$transactionId);
				$input->SetKV('out_trade_no',$orderId);
				$input->SetKV('out_refund_no',$orderId.'T'. date("YmdHis"));
				$input->SetKV('total_fee',$ordertotal>0?$ordertotal*100:0);
				$input->SetKV('refund_fee',$amount>0?$amount*100:0);
				$input->SetKV('currencyCode',$orderCurrencyCode);
				$input->SetAppid($app_id);
				//公众账号ID
				$input->SetAppkey($app_key);
				//公众账号ID
				$input->SetMch_id($merchant_id);
				//商户号
				$input->SetNonce_str($input->getNonceStr());
				//随机字符串
				//签名
				$input->SetSign();
				$xml = $input->stringToXml();
				$url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
				$response = $this->postXmlCurl($xml, $url, false, 10,$sslpath);
			/*	$response ='<xml>
				   <return_code><![CDATA[SUCCESS]]></return_code>
				   <return_msg><![CDATA[OK]]></return_msg>
				   <appid><![CDATA[wx2421b1c4370ec43b]]></appid>
				   <mch_id><![CDATA[10000100]]></mch_id>
				   <nonce_str><![CDATA[NfsMFbUFpdbEhPXP]]></nonce_str>
				   <sign><![CDATA[B7274EB9F8925EB93100DD2085FA56C0]]></sign>
				   <result_code><![CDATA[SUCCESS]]></result_code>
				   <transaction_id><![CDATA[1008450740201411110005820873]]></transaction_id>
				   <out_trade_no><![CDATA[1415757673]]></out_trade_no>
				   <out_refund_no><![CDATA[1415701182]]></out_refund_no>
				   <refund_id><![CDATA[2008450740201411110000174436]]></refund_id>
				   <refund_fee>1</refund_fee>
				</xml>';*/
				if(  $enable_log && $dumpFile = fopen($basepath .'/var/log/WechatPay.log', 'a+')) {
					fwrite($dumpFile, "\r\n".date("Y-m-d H:i:s").' : CreditMemo Online Refund Order: '.$orderId.' > '.$transactionId."\r\n"."Request: ".($xml)."\r\n"."Response: ".($response) );
				}
				if(!$this->xml_parser($response)){
					throw new \Magento\Framework\Exception\LocalizedException(__(str_replace(array("\r","\n"),"",strip_tags($response))));
					return '';
				}
				$result = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
				$url2 = '';
				
				$returnArray=array();
				if(isset($result['return_code']) && $result['return_code']!='SUCCESS') {
					//$message=$objectManager->create('\Magento\Framework\Message\ManagerInterface');
					throw new \Magento\Framework\Exception\LocalizedException( (isset($result['return_msg'])?$result['return_msg']:__("Error")));
					return '';
				} elseif(isset($result['return_code']) && $result['return_code']=='SUCCESS') {
					$insertsql = "insert into `" . $tableName . "` (create_time,order_id,token_value,status) values (now(),'" . $orderId . "','" . $result['transaction_id'] . "','REFUND')";
					$core_write->query($insertsql);
					$message=$objectManager->create('\Magento\Framework\Message\ManagerInterface');
					$message->addSuccess('Wechat Refunded Successfully');
					return '';
				}
			}
		}
		return $this;
	}
	public function xml_parser($str){
		$xml_parser = xml_parser_create();
		if(!xml_parse($xml_parser,$str,true)){
		  xml_parser_free($xml_parser);
		  return false;
		}else {
		  return (json_decode(json_encode(simplexml_load_string($str)),true));
		}
	}
}