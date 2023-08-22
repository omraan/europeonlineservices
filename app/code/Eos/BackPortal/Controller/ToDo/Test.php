<?php

namespace Eos\BackPortal\Controller\ToDo;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Eos\Base\Helper\Api;
use Eos\Base\Model\SfToken;

// Composition implements an action interface. Common interfaces to implement:
// Create - HttpPutActionInterface
// Read - HttpGetActionInterface
// Update - HttpPostActionInterface
// Delete - HttpDeleteActionInterface
class Test implements HttpGetActionInterface
{
    /** @var PageFactory */
    private $pageFactory;

    /**
     * @var Api
     */
    protected $apiHelper;

    /**
     * @var SfToken
     */
    protected $sfToken;

    // Instantiating the Context object is no longer required
    public function __construct(
        PageFactory $pageFactory,
        Api $apiHelper,
        SfToken $sfToken
    ) {
        // Calling parent::__construct() is also no longer needed
        $this->pageFactory = $pageFactory;
        $this->apiHelper = $apiHelper;
        $this->sfToken = $sfToken;
    }

    public function execute()
    {
        $credentials = $this->apiHelper->getSfCredentials();
        $token = $this->sfToken->getAccessToken();

        $appKey = $credentials['app_key'];
        $authCode = $credentials['auth_code'];
        $encodingAesKey = $credentials['app_aes_secret'];

        $timeStamp = round(microtime(true) * 1000);
        $nonce = round(microtime(true) * 1000);
        $reqBody = '{
            "customerCode": "ICRME000SRN93", 
            "declaredCurrency": "EUR", 
            "declaredValue": 10, 
            "customerOrderNo": "EOS' . strval(random_int(4,1000000)) .'", 
            "interProductCode": "INT0014", 
            "agentWaybillNo": "AG202007060053",
            "paymentInfo": {
                "payMethod": "1", 
                "taxPayMethod": "2"
            }, 
            "parcelInfoList": [
                {
                    "amount": 1, 
                    "brand": "", 
                    "currency": "EUR", 
                    "goodsDesc": "", 
                    "hsCode": "", 
                    "name": "iPhone", 
                    "quantity": 1, 
                    "unit": "pcs"
                }
            ], 
            "receiverInfo": {
                "address": "新加坡 详细地址", 
                "cargoType": 1, 
                "certCardNo": "430527199309204211", 
                "certType": "001", 
                "company": "", 
                "contact": "李想", 
                "country": "CN", 
                "email": "iuop@test.com", 
                "eori": "", 
                "phoneAreaCode": "86", 
                "phoneNo": "18888800000", 
                "postCode": "536727", 
                "telAreaCode": "86", 
                "telNo": "9516168888", 
                "vat": "",
                "regionFirst": "guang dong",
                "regionSecond": "shen zhen",
                "regionThird": "bao an"
            }, 
            "senderInfo": {
                "address": "Dennenlaan 9", 
                "cargoType": 1, 
                "certCardNo": "", 
                "company": "Europe Online Services", 
                "contact": "Onno Mallant", 
                "country": "NL", 
                "email": "onno.mallant@europeonlineservices.com", 
                "phoneAreaCode": "31", 
                "phoneNo": "1234567890", 
                "postCode": "5271", 
                "telAreaCode": "86", 
                "telNo": "9516168888", 
                "regionFirst": "Sint-Michielsgestel",
                "regionSecond": "Noord-brabant"
            }
        }';


        $pc = new \Eos\Base\Helper\SF\BizMsgCrypt($token, $encodingAesKey, $appKey);
        print_r(json_encode(json_decode($reqBody)));
        $result = $pc->encryptMsg(json_encode(json_decode($reqBody)), $timeStamp, $nonce);
        var_dump($result);

        $encrypt = $result['encrypt'];
        $signature = $result['signature'];

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api-ifsp-sit.sf.global/openapi/api/dispatch',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $encrypt,
        CURLOPT_HTTPHEADER => array(
            'appKey:'.$appKey,
            'authCode:'.$authCode,
            'token:'.$token,
            'timestamp:'.$timeStamp,
            'nonce:'.$nonce,
            'signature:'.$signature,
            'msgType: IUOP_CREATE_ORDER',
            'lang: en',
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);
        $responseDecoded = json_decode($response);
        print_r($responseDecoded);
        curl_close($curl);
        $deResult = $pc->decryptMsg($responseDecoded->apiTimestamp, $nonce, $responseDecoded->apiResultData);
        print_r($deResult);

    }
}
