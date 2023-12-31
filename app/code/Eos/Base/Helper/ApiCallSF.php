<?php
namespace Eos\Base\Helper;

use Eos\Base\Helper\ApiHelper;
use Eos\Base\Model\ResourceModel\Order\Collection as OrderCollection;
use Eos\Base\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Eos\Base\Model\ResourceModel\Parcel\Collection as ParcelCollection;
use Eos\Base\Model\ResourceModel\Parcel\CollectionFactory as ParcelCollectionFactory;
use Eos\Base\Model\ResourceModel\Shipment\Collection as ShipmentCollection;
use Eos\Base\Model\ResourceModel\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Eos\Base\Model\ShipmentFactory;
use Eos\Base\Model\OrderFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Eos\Base\Helper\Email;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\UrlInterface;
use Eos\Base\Helper\Api;
use Eos\Base\Model\SfToken;
use Magento\Framework\Simplexml\Element;
use Magento\Customer\Model\SessionFactory;

class ApiCallSF extends AbstractHelper
{
    /**
     * @var ShipmentFactory
     */
    protected $shipment;

    /**
     * @var OrderFactory
     */
    protected $order;

    /**
     * @var ProductFactory
     */

    protected $productFactory;

    /**
     * @var Product
     */

    protected $product;

    /**
     * @var AddressFactory
     */

    protected $addressFactory;

    /**
     * @var OrderCollectionFactory
     */

    protected $orderCollectionFactory;

    /**
     * @var ShipmentCollectionFactory
     */
    protected $shipmentCollectionFactory;

    /**
     * @var ParcelCollectionFactory
     */
    protected $parcelCollectionFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /** @var Email */

    protected $helperEmail;

    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var UrlInterface
     */

    protected $url;
    /**
     * @var ApiHelper
     */
    protected $apiHelper;

    /**
     * @var $sfToken
     */
    protected $sfToken;
    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;
    /**
     * @var SessionFactory
     */
    protected $customerSession;

    public function __construct(
        Context $context,
        ShipmentFactory $shipment,
        OrderFactory $order,
        Product $product,
        AddressFactory $addressFactory,
        ShipmentCollectionFactory $shipmentCollectionFactory,
        OrderCollectionFactory $orderCollectionFactory,
        ParcelCollectionFactory $parcelCollectionFactory,
        CustomerRepositoryInterface $customerRepositoryInterface,
        Email $emailHelper,
        ResponseFactory $responseFactory,
        UrlInterface $url,
        Api $apiHelper,
        SfToken $sfToken,
        AddressRepositoryInterface $addressRepository,
        SessionFactory $customerSession
    ) {
        $this->shipment = $shipment;
        $this->order = $order;
        $this->product = $product;
        $this->addressFactory = $addressFactory;
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->parcelCollectionFactory = $parcelCollectionFactory;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->emailHelper = $emailHelper;
        $this->responseFactory = $responseFactory;
        $this->url = $url;
        $this->apiHelper = $apiHelper;
        $this->sfToken = $sfToken;
        $this->addressRepository = $addressRepository;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    public function sfCreateShipment($shipmentId)
    {

        $shipmentOrders = $this->shipmentCollectionFactory->create()->getShipmentOrdersDetails()->addFieldToFilter('main_table.entity_id', ['eq' => $shipmentId]);
        $shipment = $shipmentOrders->getFirstItem();
        $credentials = $this->apiHelper->getSfCredentials();
        $token = $this->sfToken->getAccessToken();
        // $token = "auth_fba5c391-a751-4f47-a394-c6f0bb3c4130_1638339241848";
        $appKey = $credentials['app_key'];
        $authCode = $credentials['auth_code'];
        $encodingAesKey = $credentials['app_aes_secret'];

        $customer = $this->customerSession->create()->getCustomer();
        $billingId = $customer->getDefaultBilling();
        $address = $this->addressRepository->getById($billingId);

        $timeStamp = round(microtime(true) * 1000);
        $nonce = round(microtime(true) * 1000);
        $body = '{
            "customerCode": "ICRME000SRN93", 
            "declaredCurrency": "EUR", 
            "declaredValue": 10, 
            "customerOrderNo": "EOS' . strval(random_int(4, 1000000)) . '", 
            "interProductCode": "INT0014", 
            "agentWaybillNo": "AG202007060053",
            "paymentInfo": {
                "payMethod": "1", 
                "taxPayMethod": "2"
            }, 
            "parcelInfoList": [';
        $i = 0;
        foreach ($shipmentOrders->getItems() as $row) {
            $i++;
            $body .= '{
                "amount": ' . $row['product_price_gross'] . ', 
                "brand": "' . $row['product_brand'] . '", 
                "currency": "EUR", 
                "goodsDesc": "", 
                "hsCode": "' . $row['hs_cn'] . '", 
                "name": "' . $row['product_title'] . '", 
                "quantity": ' . $row['product_amount'] . ', 
                "unit": "' . $row['product_type'] . '"
            }';
            $body .= $i !== $shipmentOrders->count() ? "," : "";

        }

        $body .= '], 
            "receiverInfo": {
                "address": "' . $address->getStreet()[1] . '", 
                "cargoType": 1, 
                "certCardNo": "440183198107152114", 
                "certType": "001", 
                "company": "", 
                "contact": "' . $customer->getName() . '", 
                "country": "' . $address->getCountryId() . '", 
                "email": "' . $customer->getEmail() . '", 
                "eori": "", 
                "phoneAreaCode": "86", 
                "phoneNo": "' . $address->getTelephone() . '", 
                "postCode": "' . $address->getPostCode() . '", 
                "telAreaCode": "86", 
                "telNo": "9516168888", 
                "vat": "",
                "regionFirst": "' . $address->getRegion()->getRegion() . '",
                "regionSecond": "' . $address->getCity() . '",
                "regionThird": "' . $address->getStreet()[0] . '"
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

        $bodyDecoded = json_decode($body);
        $bodyEncoded = json_encode($bodyDecoded);
        $pc = new \Eos\Base\Helper\SF\BizMsgCrypt($token, $encodingAesKey, $appKey);
        $result = $pc->encryptMsg($bodyEncoded, $timeStamp, $nonce);
        $encrypt = $result['encrypt'];
        $signature = $result['signature'];

        $headers = [
            'msgType' => "IUOP_CREATE_ORDER",
            'appKey' => $appKey,
            'authCode' => $authCode,
            'token' => $token,
            'timestamp' => $timeStamp,
            'nonce' => $nonce,
            'signature' => $signature,
            'lang' => 'en',
            'Content-Type' => 'application/json'
        ];

        $response = $this->apiHelper->makeApiPostRequest(
            $credentials['app_url'] . '/openapi/api/dispatch',
            $headers,
            $encrypt
        );

        echo "<pre>";
        $responseDecoded = json_decode($response);
        print_r($responseDecoded);
        $deResult = $pc->decryptMsg($responseDecoded->apiTimestamp, $nonce, $responseDecoded->apiResultData);
        print_r($deResult[1]);

    }

    public function ReConfrimWeightOrder($customerId, $shipment_id = null, $orders = null, $correction = false)
    {
        if (!isset($shipment_id)) {
            // Create Shipment Record
            $shipmentModel = $this->shipment->create();
            $shipmentModel->setData('status', 'open');
            $shipmentModel->setData('customer_id', $customerId);
            $shipmentModel->save();

            $shipmentId = $shipmentModel->getId();

            $shipmentModel->load($shipmentId)->setData('f_shipment_id', "EOS" . str_pad($shipmentId, 10, "0", STR_PAD_LEFT) . '_test0881');
            $shipmentModel->save();
        } else {

            // This is done in order to check later whether $shipment_id already exist before this function call.
            $shipmentId = $shipment_id;
            $shipmentModel = $this->shipment->create();
            $shipmentModel->load($shipmentId);
            /*
            $c = explode("_c", $shipmentModel->getData('f_shipment_id'));

            if(isset($c[1])) {
                $c = "_c" . (intval($c[1]) + 1);
            } else {
                $c = "_c1";
            }

            $shipmentModel->setData('f_shipment_id', "EOS" . str_pad($shipmentId, 10, "0", STR_PAD_LEFT) . $c);*/
            $shipmentModel->save();
            $customerId = $this->shipment->create()->load($shipment_id)->getData('customer_id');
        }

        if (isset($orders)) {
            foreach ($orders as $order) {
                $orderModel = $this->order->create();
                $orderModel->load($order['entity_id']);
                $orderModel->setData('shipment_id', $shipmentId);
                $orderModel->setData('status', 'Shipment created');
                $orderModel->save();
            }
        }

        $customer = $this->customerRepositoryInterface->getById($customerId);
        $email = $customer->getEmail();
        $address = $this->addressFactory->create()->load($customer->getDefaultBilling());

        /** @var $shipmentCollection ShipmentCollection */
        $shipmentCollection = $this->shipmentCollectionFactory->create()->getShipmentOrdersDetails()->addFieldToFilter('main_table.entity_id', $shipmentId);

        $shipments = $shipmentCollection->getItems();
        $shipmentFirst = $shipmentCollection->getFirstItem();

        /** @var $parcelCollection ParcelCollection */
        $parcelCollection = $this->parcelCollectionFactory->create()->addFieldToFilter('tracking_number', $shipmentFirst['webshop_tracking_number']);

        $parcels = $parcelCollection->getItems();
        $countParcels = 0;
        $totalWeight = 0;

        foreach ($parcels as $parcel) {
            $totalWeight += $parcel['weight'];
            $countParcels++;
        }

        $weight = $shipmentFirst['total_weight'] > 0 ? $shipmentFirst['total_weight'] : $totalWeight;

        $isChangeOrder = !isset($shipment_id) || $correction ? 1 : 2;

        //The message
        $xml_open = '<?xml version="1.0" encoding="utf-8"?>
                    <Request service="ReConfrimWeightOrder" lang="en">
                    <Head>OSMS_10840</Head>
                    <Body>
                    <Order
                        is_change_order="' . $isChangeOrder . '"
                        reference_no1="' . $shipmentFirst['f_shipment_id'] . '"
                        realweightqty="' . $weight . '"
                        rec_userno="90127712"
                        j_shippercode="AMS03A"
                        express_type="602"
                        custid="0330000030"
                        j_company="Europe Online Services"
                        j_contact="Onno Mallant"
                        j_tel="0031682274161"
                        j_mobile="0031682274161"
                        j_address="Dennenlaan 9"
                        j_province="Noord-Brabant"
                        j_city="Sint-Michielsgestel"
                        j_county=""
                        j_country="NL"
                        j_post_code="5271 RE"
                        d_company="' . $address->getData('company') . '"
                        d_contact="' . $address->getData('firstname') . " " . ($address->getData('middlename') ? $address->getData('middlename') . " " : "") . $address->getData('lastname') . '"
                        d_tel="' . $address->getData('telephone') . '"
                        d_mobile="' . $address->getData('telephone') . '"
                        d_address="' . str_replace("\n", " ", $address->getData('street')) . '"
                        d_province="' . $address->getData('region') . '"
                        d_city="' . $address->getData('city') . '"
                        d_email="' . $email . '"
                        d_county=""
                        d_country="' . $address->getData('country_id') . '"
                        d_post_code="' . $address->getData('postcode') . '"
                        currency="EUR"
                        parcel_quantity="' . $countParcels . '"
                        pay_method="1"
                        tax_pay_type="2">';

        $xml_middle = '';

        // Since eos_hs_product.product_title and eos_order_details.product_title have the same column name in ShipmentsCollection,
        // eos_order_details.product_title has been changed to product_name
        foreach ($shipments as $cargo) {
            $xml_middle .= '<Cargo
                product_record_no="' . $cargo['product_tax_nr'] . '"
                name="' . $cargo['product_name'] . '"
                count="' . $cargo['product_amount'] . '"
                brand="' . $cargo['product_brand'] . '"
                currency="' . $cargo['webshop_currency'] . '"
                unit="' . $cargo['product_type'] . '"
                amount="' . $cargo['product_price_net'] . '"
                specifications="1"
                good_prepard_no="' . $cargo['product_tax_nr'] . '"
                source_area="NL"
                />';
        }

        $xml_close = '</Order>
            </Body>
            </Request>';

        $xml = $xml_open . $xml_middle . $xml_close;


        //API Key
        $checkword = '01b2832ae2024a28';

        //base64 Encryption
        $data = base64_encode($xml);

        //Generating the validation string
        $validateStr = base64_encode(md5($xml . $checkword, false));

        //request URL
        $pmsLoginAction = 'https://osms.sf-express.com/osms/services/OrderWebService?wsdl';

        $client = new \SoapClient($pmsLoginAction);
        $client->__setLocation($pmsLoginAction);
        $result = $client->sfexpressService(['data' => $data, 'validateStr' => $validateStr, 'customerCode' => 'OSMS_10840']);

        $data = json_decode(json_encode($result), true);
        $simpleXml = new \SimpleXMLElement($data['Return']);


        if (strval($simpleXml->xpath('/Response/Head')[0]) == "OK") {

            $resultArray['customerOrderNo'] = strval($simpleXml->xpath('/Response/Body/OrderResponse/customerOrderNo')[0]);
            $resultArray['awbNo'] = strval($simpleXml->xpath('/Response/Body/OrderResponse/mailNo')[0]);
            $resultArray['originCode'] = strval($simpleXml->xpath('/Response/Body/OrderResponse/originCode')[0]);
            $resultArray['destCode'] = strval($simpleXml->xpath('/Response/Body/OrderResponse/destCode')[0]);
            $resultArray['printUrl'] = strval($simpleXml->xpath('/Response/Body/OrderResponse/printUrl')[0]);
            $resultArray['invoiceUrl'] = strval($simpleXml->xpath('/Response/Body/OrderResponse/invoiceUrl')[0]);

            $shipmentModel = $this->shipment->create()->load($shipmentId)->setData('awb_code', $resultArray['awbNo']);
            $shipmentModel->setData('originCode', $resultArray['originCode']);
            $shipmentModel->setData('destCode', $resultArray['destCode']);
            $shipmentModel->setData('printUrl', $resultArray['printUrl']);
            $shipmentModel->setData('invoiceUrl', $resultArray['invoiceUrl']);
            $shipmentModel->save();
            $return['success'] = true;

        } else {
            $message = strval($simpleXml->xpath('/Response/ERROR')[0]);
            $CustomRedirectionUrl = $this->url->getUrl('portal/shipment/error', ['message' => $message]);
            $this->responseFactory->create()->setRedirect($CustomRedirectionUrl)->sendResponse();
            $templateVars['type_error'] = isset($shipment_id) ? "SF API Call after payment" : "SF First API Call";
            $templateVars['message'] = $message;
            $templateVars['customer_id'] = $customerId;
            $templateVars['customer_email'] = $email;
            $this->helperEmail->sendErrorEmail(8, $templateVars);
            $return['success'] = false;

        }

        $return['shipment_id'] = $shipmentId;
        return $return;
    }
    public function CancelOrderService($shipmentId)
    {

        $shipmentModel = $this->shipment->create()->load($shipmentId);
        $awbCode = $shipmentModel->getData('awb_code');

        $xml = '<?xml version="1.0"?>
                 <Request service="CancelOrderService" lang="en">
                       <Head>OSMS_10840</Head>
                       <Body>
                         <CancelOrder mailno="' . $awbCode . '"/>
                       </Body>
                 </Request>';

        //API Key
        $checkword = '01b2832ae2024a28';

        //base64 Encryption
        $data = base64_encode($xml);

        //Generating the validation string
        $validateStr = base64_encode(md5($xml . $checkword, false));

        //request URL
        $pmsLoginAction = 'https://osms.sf-express.com/osms/services/OrderWebService?wsdl';

        $client = new \SoapClient($pmsLoginAction);
        $client->__setLocation($pmsLoginAction);
        $result = $client->sfexpressService(['data' => $data, 'validateStr' => $validateStr, 'customerCode' => 'OSMS_10840']);

        $data = json_decode(json_encode($result), true);
        $simpleXml = new \SimpleXMLElement($data['Return']);


        if (strval($simpleXml->xpath('CancelOrderResponse')[0]['result'][0]) == "true") {

            $shipmentModel->setData('awb_code', '')->save();
            $return['success'] = true;
            $return['message'] = "Success";

        } else {
            $return['success'] = false;
            $return['message'] = strval($simpleXml->xpath('CancelOrderResponse')[0]['message'][0]);
        }

        $return['shipment_id'] = $shipmentId;
        return $return;
    }

    public function getAwbStatus($shipmentId)
    {
        /** @var $shipmentCollection ShipmentCollection */
        $shipmentCollection = $this->shipmentCollectionFactory->create();
        $shipmentCollection->addFieldToFilter('entity_id', ['eq' => $shipmentId]);
        $awbCode = $shipmentCollection->getFirstItem()['awb_code'];
        $xml = '<?xml version="1.0"?>
                    <Request service="RouteService" lang="en">
                      <Head>OSMS_10840</Head>
                      <Body>
                        <Route tracking_type="1" tracking_number="' . $awbCode . '"/>
                      </Body>
                  </Request>';

        //API Key
        $checkword = '01b2832ae2024a28';
        //base64 Encryption
        $data = base64_encode($xml);

        //Generating the validation string
        $validateStr = base64_encode(md5($xml . $checkword, false));

        //request URL
        $pmsLoginAction = 'https://osms.sf-express.com/osms/services/OrderWebService?wsdl';

        try {
            $client = new \SoapClient($pmsLoginAction);
            $client->__setLocation($pmsLoginAction);
            $result = $client->sfexpressService(['data' => $data, 'validateStr' => $validateStr, 'customerCode' => 'OSMS_10840']);

            $data = json_decode(json_encode($result), true);
            $simpleXml = new \SimpleXMLElement($data['Return']);
            $resultArray = $simpleXml->xpath('/Response/Body/RouteResponse/Route');

        } catch (Exception $e) {
            exit($e);
        }

        return $resultArray;

    }
    public function getGts($shipmentId)
    {

        /** @var $shipmentCollection ShipmentCollection */
        $shipmentCollection = $this->shipmentCollectionFactory->create();
        $shipmentCollection->addFieldToFilter('entity_id', ['eq' => $shipmentId]);
        $awbCode = $shipmentCollection->getFirstItem()['awb_code'];

        $url = 'https://ibu-gts.sf-express.com/gts-tc/api/track/receive';
        $sysCode = 'EOS-system';
        $sign = '7f03f3328fd8b4241e0b04724d0a01d36fad74d68373b2f3d67446353766894c';

        $dateTime = date("Y-m-d H:i:s");
        $arrData[0] = [
            'sfWaybillNo' => $awbCode,
            //dummy number for testing,
            'opCode' => '30',
            'zoneCode' => 'AMS03A',
            'opAttachInfo' => 'AMS03A027Z0900',
            'barOprCode' => '90087350',
            'contnrCode' => '390074476529',
            'gmt' => 'GMT+1',
            'localTm' => $dateTime,
            'trackCountry' => 'NL',
            'trackProvince' => '',
            'trackCity' => 'Amsterdam',
        ];
        $arrMessage = array(
            'sysCode' => $sysCode,
            'sign' => $sign,
            'billNoType' => 1,
            'data' => $arrData,
        );
        $headers = ['Content-Type: application/json'];
        $jsonMessage = json_encode($arrMessage, JSON_PRETTY_PRINT);

        $response = json_decode($this->postIuopApiQuery($headers, $jsonMessage, $url));
        echo "<pre>";
        print_r($response);


    }
    public function postIuopApiQuery($headers, $json, $apiURL)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiURL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json); //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);
        return $server_output;
    }

}