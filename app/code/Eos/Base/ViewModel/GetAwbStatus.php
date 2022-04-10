<?php
namespace Eos\Base\ViewModel;

use Eos\Base\Model\ResourceModel\Shipment\Collection as ShipmentCollection;
use Eos\Base\Model\ResourceModel\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\HTTP\Client\Curl;
use GuzzleHttp\Client as GuzzleClient;

class GetAwbStatus implements ArgumentInterface
{

    /**
     * @var ShipmentCollectionFactory
     */
    protected $shipmentCollectionFactory;

    /**
     * @var SessionFactory
     */
    protected $customerSession;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var GuzzleClient
     */

    protected $_guzzleClient;

    public function __construct(
        ShipmentCollectionFactory $shipmentCollectionFactory,
        SessionFactory $customerSession,
        Curl $curl,
        GuzzleClient $guzzleClient
    ) {
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->customerSession = $customerSession;
        $this->curl = $curl;
        $this->_guzzleClient = $guzzleClient;
    }
    public function getAwbCode($shipment_id)
    {
        /** @var $shipmentCollection ShipmentCollection */
        $shipmentCollection = $this->shipmentCollectionFactory->create();
        $shipmentCollection->addFieldToFilter('entity_id', ['eq' => $shipment_id]);
        $awbCode = $shipmentCollection->getFirstItem()['awb_code'];


        return $awbCode;

    }


    public function getAwbStatus($shipment_id)
    {
        /** @var $shipmentCollection ShipmentCollection */
        $shipmentCollection = $this->shipmentCollectionFactory->create();
        $shipmentCollection->addFieldToFilter('entity_id', ['eq' => $shipment_id]);
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

    public function getGts($shipment_id) {

        /** @var $shipmentCollection ShipmentCollection */
        $shipmentCollection = $this->shipmentCollectionFactory->create();
        $shipmentCollection->addFieldToFilter('entity_id', ['eq' => $shipment_id]);
        $awbCode = $shipmentCollection->getFirstItem()['awb_code'];

        $url = 'https://ibu-gts.sf-express.com/gts-tc/api/track/receive';
        $sysCode ='EOS-system';
        $sign ='7f03f3328fd8b4241e0b04724d0a01d36fad74d68373b2f3d67446353766894c';

        $dateTime = date("Y-m-d H:i:s");
        $arrData[0] = [
            'sfWaybillNo' => $awbCode,   //dummy number for testing,
            'opCode' => '30',
            'zoneCode' => 'AMS03A',
            'opAttachInfo' => 'AMS03A027Z0900',
            'barOprCode' => '90087350',
            'contnrCode' => '390074476529',
            'gmt' => 'GMT+1',
            'localTm' => $dateTime ,
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
        $jsonMessage = json_encode($arrMessage,JSON_PRETTY_PRINT);

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
        curl_setopt($ch, CURLOPT_POSTFIELDS,$json);  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec ($ch);
        curl_close ($ch);
        return $server_output;
    }

    /**
     * Get message for no orders.
     *
     * @return Phrase
     * @since 102.1.0
     */
    public function getEmptyItemsMessage()
    {
        return __('There are no orders placed or your parcel(s) have not arrived yet.');
    }
}
