<?php


namespace Sunflowerbiz\Wechat\Block\Redirect;
use \Sunflowerbiz\Wechat\Helper\ObjectManager as Sunflowerbiz_OM;

class Redirect extends \Magento\Framework\View\Element\Template
{


    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var  \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \Sunflowerbiz\Wechat\Helper\Data
     */
    protected $_sunHelper;

    /**
     * @var ResolverInterface
     */
    protected $_resolver;

    /**
     * @var \Sunflowerbiz\Wechat\Logger\SunflowerbizLogger
     */
    protected $_sunLogger;


	public $data = null;
	public $_openId = '';
 /**
     * Redirect constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Sunflowerbiz\Wechat\Helper\Data $sunHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = [],
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Sunflowerbiz\Wechat\Helper\Data $sunHelper,
        \Magento\Framework\Locale\ResolverInterface $resolver
    ) {
        $this->_orderFactory = $orderFactory;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
		
        $this->_sunHelper = $sunHelper;
        $this->_resolver = $resolver;
		
		
		/*
		if($_SERVER['HTTP_HOST']=='127.0.0.1')
		   $incrementId ='000000011';
            $this->_order = $this->_orderFactory->create()->loadByIncrementId($incrementId);
		}*/
        if (!$this->_order) {
            $incrementId = $this->_getCheckout()->getLastRealOrderId();
            $this->_order = $this->_orderFactory->create()->loadByIncrementId($incrementId);
        }
		
    }
	public function getOrderId(){
		$incrementId=$this->_order->getIncrementId();
		return $incrementId;
	}
	
	public function getIp(){    
		$ip = '';    
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){        
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];    
		}elseif(isset($_SERVER['HTTP_CLIENT_IP'])){        
			$ip = $_SERVER['HTTP_CLIENT_IP'];    
		}else{        
			$ip = $_SERVER['REMOTE_ADDR'];    
		}
		$ip_arr = explode(',', $ip);
		return $ip_arr[0];
	}
	
	 public function getQrimage()
    {
	
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
		$base  =  $directory->getRoot();
   		date_default_timezone_set('Asia/Shanghai');
		
      
        $input =Sunflowerbiz_OM::getObjectManager()->create('\Sunflowerbiz\Wechat\Model\Unifiedorder');
		
		$storename=$this->_storeManager->getStore()->getName();
		$orderid=$this->_order->getIncrementId();
		$ordertotal=$this->_order->getGrandTotal();
		$notifyurl=$this->_urlBuilder->getUrl('wechat/process/notify');
		
	
		$ip=$this->getIp();
        $input->SetBody($storename);
        $input->SetAttach($orderid);
        $input->SetOut_trade_no($orderid);
        $input->SetTotal_fee((int) ($ordertotal * 100));
        $input->SetTime_start(date('YmdHis', strtotime(date('Y-m-d H:i:s')) + 3600*0  ) );
        $input->SetTime_expire(date('YmdHis', strtotime(date('Y-m-d H:i:s')) + 3600));
        $input->SetGoods_tag($orderid);
		
        $input->SetNotify_url($notifyurl);
        $input->SetTrade_type("NATIVE");
		
        $input->SetProduct_id($orderid);
		

		$app_id=  $this->_scopeConfig->getValue('payment/wechatpayment/app_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	
		$merchant_id=  $this->_scopeConfig->getValue('payment/wechatpayment/merchant_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$app_key=  $this->_scopeConfig->getValue('payment/wechatpayment/app_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if($app_id=="" || $merchant_id=="" || $app_key=="")
		return __('Payment Config Error. Please contact owner.');
	  
	    $input->SetAppid($app_id);//公众账号ID
		$input->SetAppkey($app_key);//公众账号ID
        $input->SetMch_id($merchant_id);//商户号
        $input->SetSpbill_create_ip($ip);//终端ip
        $input->SetNonce_str($this->getNonceStr());//随机字符串
	
        //签名
        $input->SetSign();
		
        $xml = $input->stringToXml();
        $timeOut = 10;
	
		$active_log_stock_update =$this->_scopeConfig->getValue('payment/wechatpayment/enable_log', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if($active_log_stock_update){
							if( $dumpFile = fopen($base .'/var/log/WechatPay.log', 'a+')){
								fwrite($dumpFile, date("Y-m-d H:i:s").' : Request QrCode Data: '."\r\n");
								fwrite($dumpFile, $xml." ; ");
							}
		}

		
		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
		
      	 $response = $this->postXmlCurl($xml, $url, false, $timeOut);
		$result = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        $url2 = '';
		if($active_log_stock_update){
						if( $dumpFile = fopen($base .'/var/log/WechatPay.log', 'a+')){
						 fwrite($dumpFile, date("Y-m-d H:i:s").' : Return QrCode Data: '."\r\n");
							 foreach ($result as $k => $v) {
										fwrite($dumpFile, $k.'->'.$v." ; ");
								}
						 }
			}
	
	
        if (isset($result["code_url"])) {
            return $result["code_url"];
		}else
		return '';
		
    }
	
	 public function getH5pay()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
		$base  =  $directory->getRoot();
   		date_default_timezone_set('Asia/Shanghai');
		
      
        $input =Sunflowerbiz_OM::getObjectManager()->create('\Sunflowerbiz\Wechat\Model\Unifiedorder');
		
		$storename=$this->_storeManager->getStore()->getName();
		$orderid=$this->_order->getIncrementId();
		$ordertotal=$this->_order->getGrandTotal();
		$notifyurl=$this->_urlBuilder->getUrl('wechat/process/notify');
		
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$core_write = $resource->getConnection();
		$tableName = $resource->getTableName('sun_wechat_history');
			
		$token_value="";
		$selectsql= "select * from `".$tableName."` where order_id='".$orderid."' and status='SUCCESS'";
		$wechat_history=$core_write->fetchAll($selectsql);
		if(count($wechat_history)>0){			
					return '<script>location.href = "'. $this->getSuccessUrl().'";</script>';
		}
	
		$ip=$this->getIp();
        $input->SetBody($storename);
        $input->SetAttach($orderid);
        $input->SetOut_trade_no($orderid);
        $input->SetTotal_fee((int) ($ordertotal * 100));
        $input->SetTime_start(date('YmdHis', strtotime(date('Y-m-d H:i:s')) + 3600*0  ) );
        $input->SetTime_expire(date('YmdHis', strtotime(date('Y-m-d H:i:s')) + 3600));
        $input->SetGoods_tag($orderid);
		
        $input->SetNotify_url($notifyurl);
        $input->SetTrade_type("MWEB");
		
        $input->SetProduct_id($orderid);
		

		$app_id=  $this->_scopeConfig->getValue('payment/wechatpayment/app_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	
		$merchant_id=  $this->_scopeConfig->getValue('payment/wechatpayment/merchant_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$app_key=  $this->_scopeConfig->getValue('payment/wechatpayment/app_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if($app_id=="" || $merchant_id=="" || $app_key=="")
		return __('Payment Config Error. Please contact owner.');
	  
	    $input->SetAppid($app_id);//公众账号ID
		$input->SetAppkey($app_key);//公众账号ID
        $input->SetMch_id($merchant_id);//商户号
        $input->SetSpbill_create_ip($ip);//终端ip
        $input->SetNonce_str($this->getNonceStr());//随机字符串
		
		$input->SetKV("scene_info",'{"h5_info": {"type":"Wap","wap_url": "'.$this->_urlBuilder->getUrl("/").'","wap_name": "'.$storename.'"}}');
	
        //签名
        $input->SetSign();
		
        $xml = $input->stringToXml();
        $timeOut = 10;
	
		$active_log_stock_update =$this->_scopeConfig->getValue('payment/wechatpayment/enable_log', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if($active_log_stock_update){
							if( $dumpFile = fopen($base .'/var/log/WechatPay.log', 'a+')){
								fwrite($dumpFile, date("Y-m-d H:i:s").' : Request H5 Pay Data: '."\r\n");
								fwrite($dumpFile, $xml." ; ");
							}
		}

		
		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
		
      	 $response = $this->postXmlCurl($xml, $url, false, $timeOut);
		$result = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        $url2 = '';
		if($active_log_stock_update){
						if( $dumpFile = fopen($base .'/var/log/WechatPay.log', 'a+')){
						 fwrite($dumpFile, date("Y-m-d H:i:s").' : Return H5 Pay Data: '."\r\n");
							 foreach ($result as $k => $v) {
										fwrite($dumpFile, $k.'->'.$v." ; ");
								}
						 }
			}
	
	//$result["mweb_url"]='https://wx.tenpay.com/cgi-bin/mmpayweb-bin/checkmweb?prepay_id=wx2016121516420242444321ca0631331346&package=1405458241';
       
		if (isset($result["mweb_url"])) {
		 	//Header("Location: ".$result["mweb_url"]);
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $core_write = $resource->getConnection();
            $tableName = $resource->getTableName('sun_wechat_history');
			$selectsql= "select * from `".$tableName."` where order_id='".$orderid."' and status='WECHATH5'";
			$wechat_history=$core_write->fetchAll($selectsql);
			if(count($wechat_history)<=0){	
					$insertsql = "insert into `" . $tableName . "` (create_time,order_id,token_value,status) values (now(),'" . $orderid . "','','WECHATH5')";
            		$core_write->query($insertsql);
				  return '<script> window.location.href = "'.$result["mweb_url"].'";</script>';	
			}else{
			$insertsql = "delete from `" . $tableName . "` where order_id='" . $orderid . "' and status='WECHATH5'";
            $core_write->query($insertsql);
			return "";
			}
        
        }else
		return '';
		
    }
	
	
	public function getSanEventUrl()
    {
        $url= $this->_urlBuilder->getUrl("wechat/process/scanevent");
		if(substr($url,-1)=='/')  $url=substr($url,0,strlen($url)-1); 
		return $url;
    }

  	 public function getFailureUrl()
    {
        return $this->_urlBuilder->getUrl("checkout/onepage/failure");
    }

 	 public  function getSuccessUrl()
    {
        return $this->_urlBuilder->getUrl("checkout/onepage/success");
    }


    public static function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, random_int(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
	
	public function getContinueUrl(){
		return $this->_urlBuilder->getUrl();
	}
	
    public function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);

        curl_setopt($ch, CURLOPT_URL, $url);
		if($_SERVER['HTTP_HOST']=='127.0.0.1') {		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}else{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);//严格校验
		}
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
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
	
	//for jsapi in wechat browser
	public function use_h5pay(){ 
		$enable_h5pay=  $this->_scopeConfig->getValue('payment/wechatpayment/enable_h5pay', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if($enable_h5pay && $this->is_mobile_request() && !$this->is_weixin())
			return true;
		else
			return false;
	}
	
	//for jsapi in wechat browser
	public function use_jsapi(){ 
		$enable_jsapi=  $this->_scopeConfig->getValue('payment/wechatpayment/enable_jsapi', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if($enable_jsapi && $this->is_weixin())
			return true;
		else
			return false;
	}
	
	
	public function is_weixin(){ 
	//if($_SERVER['HTTP_HOST']=='127.0.0.1')   return true;
	if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
			return true;
	}	
		return false;
	}
	
	 public function GetOpenid()
    {
        
        $code =  $this->getRequest()->getParam('code');
        if (!$code){
			$baseUrl=$this->_urlBuilder->getUrl('wechat/process/redirect');
            $url = $this->__CreateOauthUrlForCode($baseUrl);
            Header("Location: $url");
			 $this->getResponse()->setBody("<script>window.location.href='".$url."'+'?rand='+Math.random()</script>");
            return "";
        } else {
            
            $this->_openId = $this->getOpenidFromMp($code);
            return $this->_openId;
        }
    }
	
	
	
	 public function GetOpenidFromMp($code)
    {
        $url = $this->__CreateOauthUrlForOpenid($code);

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
	
		 
        $this->data = $data;
		 $openid ='';
		if(isset($data['openid']))
        $openid = $data['openid'];
			 
        return $openid;
    }
	
	public function __CreateOauthUrlForCode($redirectUrl)
    {	
		$app_id=  $this->_scopeConfig->getValue('payment/wechatpayment/app_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $urlObj["appid"] =$app_id;
        $urlObj["redirect_uri"] = "$redirectUrl";
        $urlObj["response_type"] = "code";
        $urlObj["scope"] = "snsapi_base";
        $urlObj["state"] = "STATE" . "#wechat_redirect";
        $bizString = $this->ToUrlParams($urlObj);
		
	
        return "https://open.weixin.qq.com/connect/oauth2/authorize?" . $bizString;
    }
	
	  private function __CreateOauthUrlForOpenid($code)
    {
		$app_id=  $this->_scopeConfig->getValue('payment/wechatpayment/app_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
     	$app_secret=  $this->_scopeConfig->getValue('payment/wechatpayment/app_secret', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
       
	   
        $urlObj["appid"] = $app_id;
        $urlObj["secret"] = $app_secret;
        $urlObj["code"] = $code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = $this->ToUrlParams($urlObj);
		 
        return "https://api.weixin.qq.com/sns/oauth2/access_token?" . $bizString;
    }
	
	 public function getJsApiParameters()
    {
		date_default_timezone_set('Asia/Shanghai');
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
		$base  =  $directory->getRoot();
	  	$input =Sunflowerbiz_OM::getObjectManager()->create('\Sunflowerbiz\Wechat\Model\Unifiedorder');
		  
		$storename=$this->_storeManager->getStore()->getName();
		$orderid=$this->_order->getIncrementId();
		$ordertotal=$this->_order->getGrandTotal();
		$notifyurl=$this->_urlBuilder->getUrl('wechat/process/notify');
		
        $input->SetBody($storename);
        $input->SetAttach($orderid);
        $input->SetOut_trade_no($orderid);
        $input->SetTotal_fee((int) ($ordertotal * 100));
        $input->SetTime_start(date('YmdHis', strtotime(date('Y-m-d H:i:s')) + 3600*0  ) );
        $input->SetTime_expire(date('YmdHis', strtotime(date('Y-m-d H:i:s')) + 3600));
        $input->SetGoods_tag($orderid);
     	$input->SetNotify_url($notifyurl);
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($this->_openId);
		
		$app_id=  $this->_scopeConfig->getValue('payment/wechatpayment/app_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$merchant_id=  $this->_scopeConfig->getValue('payment/wechatpayment/merchant_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$app_key=  $this->_scopeConfig->getValue('payment/wechatpayment/app_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$ip=$this->getIp();
	    $input->SetAppid($app_id);//公众账号ID
		$input->SetAppkey($app_key);//公众账号ID
        $input->SetMch_id($merchant_id);//商户号
        $input->SetSpbill_create_ip($ip);//终端ip
        $input->SetNonce_str($this->getNonceStr());//随机字符串
	
        //签名
        $input->SetSign();
		
        $xml = $input->stringToXml();
        $timeOut = 10;
	
		$active_log_stock_update =$this->_scopeConfig->getValue('payment/wechatpayment/enable_log', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
				if($active_log_stock_update){
						if( $dumpFile = fopen($base .'/var/log/WechatPay.log', 'a+')){
							 fwrite($dumpFile, date("Y-m-d H:i:s").' : Request jspay Data: '."\r\n");
							fwrite($dumpFile, $xml." ; ");
						 }
					 }
		
		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
		
        $response = $this->postXmlCurl($xml, $url, false, $timeOut);
		$UnifiedOrderResult = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
   
		if($active_log_stock_update){
						if( $dumpFile = fopen($base .'/var/log/WechatPay.log', 'a+')){
						 fwrite($dumpFile, date("Y-m-d H:i:s").' : Return jspay Data: '."\r\n");
							 foreach ($UnifiedOrderResult as $k => $v) {
										fwrite($dumpFile, $k.'->'.$v." ; ");
								}
						 }
		}

        if (!array_key_exists("appid", $UnifiedOrderResult)
            || !array_key_exists("prepay_id", $UnifiedOrderResult)
            || $UnifiedOrderResult['prepay_id'] == ""
        ) {
         
			 	$errorMsg = __('Error with payment method please select different payment method.');
              throw new \Magento\Framework\Validator\Exception(__('Payment error.' .$errorMsg));
        }
		
        $timeStamp = (string)time();
		
        $jsapi = Sunflowerbiz_OM::getObjectManager()->create('\Sunflowerbiz\Wechat\Model\Jsapipay');
		
        $jsapi->SetAppid(	$UnifiedOrderResult["appid"]);
		
		$jsapi->SetAppkey($app_key);//公众账号ID
        $jsapi->SetTimeStamp($timeStamp);
        $jsapi->SetNonceStr($this->getNonceStr());
        $jsapi->SetPackage("prepay_id=" . $UnifiedOrderResult['prepay_id']);
        $jsapi->SetSignType("MD5");
        $jsapi->SetPaySign($jsapi->MakeSign(true));
        $parameters = json_encode($jsapi->GetValues());
		//print_r($parameters);
        return $parameters;
    }
	
	 public function ToUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v) {
            if ($k != "sign") {
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }


    /**
     * @return $this
     */
    public function _prepareLayout()
    {
		
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getFormUrl()
    {}

    /**
     * @return mixed
     */
    public function getPaymentMethodSelectionOnSunflowerbiz()
    {
        return $this->_sunHelper->getSunflowerbizHppConfigDataFlag('payment_selection_on_sun');
    }

    /**
     * @return array
     */
    public function getFormFields()
    {}

 	public function is_mobile_request()  
	{  
	 $_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';  
	 $mobile_browser = '0';  
	 if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT'])))  
	  $mobile_browser++;  
	 if((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') !== false))  
	  $mobile_browser++;  
	 if(isset($_SERVER['HTTP_X_WAP_PROFILE']))  
	  $mobile_browser++;  
	 if(isset($_SERVER['HTTP_PROFILE']))  
	  $mobile_browser++;  
	 $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));  
	 $mobile_agents = array(  
		'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',  
		'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',  
		'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',  
		'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',  
		'newt','noki','oper','palm','pana','pant','phil','play','port','prox',  
		'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',  
		'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',  
		'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',  
		'wapr','webc','winw','winw','xda','xda-'
		);  
	 if(in_array($mobile_ua, $mobile_agents))  
	  $mobile_browser++;  
	 if(strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false)  
	  $mobile_browser++;  
	 // Pre-final check to reset everything if the user is on Windows  
	 if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false)  
	  $mobile_browser=0;  
	 // But WP7 is also Windows, with a slightly different characteristic  
	 if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false)  
	  $mobile_browser++;  
	 if($mobile_browser>0)  
	  return true;  
	 else
	  return false;
	  }
    


    /**
     * @param null $date
     * @param string $format
     * @return mixed
     */
    protected function _getDate($date = null, $format = 'Y-m-d H:i:s')
    {
        if (strlen($date) < 0) {
            $date = date('d-m-Y H:i:s');
        }
        $timeStamp = new \DateTime($date);
        return $timeStamp->format($format);
    }


    /**
     * The character escape function is called from the array_map function in _signRequestParams
     *
     * @param $val
     * @return mixed
     */
    protected function escapeString($val)
    {
        return str_replace(':', '\\:', str_replace('\\', '\\\\', $val));
    }

    /**
     * Get frontend checkout session object
     *
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckout()
    {
        return $this->_checkoutSession;
    }
}