<?php

namespace Eos\BackPortal\Observer;



use Eos\Base\Model\OrderFactory;
use Eos\Base\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Eos\Base\Model\ShipmentFactory;
use Magento\Customer\Model\Session;
use Eos\Base\Model\ResourceModel\Parcel\Collection as ParcelCollection;
use Eos\Base\Model\ResourceModel\Parcel\CollectionFactory as ParcelCollectionFactory;
use Eos\Base\Model\ResourceModel\Shipment\Collection as ShipmentCollection;
use Eos\Base\Model\ResourceModel\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Magento\Customer\Model\AddressFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Webapi\Soap\ClientFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\SessionFactory;

class ConfirmPayment implements ObserverInterface
{

    // TODO: check sales_order_invoice_pay as observer

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * @var OrderFactory
     */
    protected $_order;

    /**
     * @var ShipmentFactory
     */
    protected $_shipment;

    /**
     * @var OrderCollectionFactory
     */

    protected $_orderCollectionFactory;


    /**
     * @var AddressFactory
     */

    protected $_addressFactory;

    /**
     * @var ShipmentCollectionFactory
     */
    protected $_shipmentCollectionFactory;

    /**
     * @var ParcelCollectionFactory
     */
    protected $_parcelCollectionFactory;

    /**
     * @var ClientFactory
     */
    protected $soapClientFactory;


    /**
     * @var SessionFactory\
     */
    protected $checkoutSessionFactory;

    public function __construct(
        ClientFactory $soapClientFactory,
        Session $_customerSession,
        ShipmentCollectionFactory $shipmentCollectionFactory,
        ParcelCollectionFactory $parcelCollectionFactory,
        AddressFactory $_addressFactory,
        DateTime $dateTime,
        OrderFactory $order,
        ShipmentFactory $shipment,
        OrderCollectionFactory $orderCollectionFactory,
        SessionFactory $checkoutSessionFactory
    ) {
        $this->_customerSession = $_customerSession;
        $this->_shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->_parcelCollectionFactory = $parcelCollectionFactory;
        $this->_addressFactory = $_addressFactory;
        $this->soapClientFactory = $soapClientFactory;
        $this->_dateTime = $dateTime;
        $this->_order = $order;
        $this->_shipment = $shipment;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->checkoutSessionFactory = $checkoutSessionFactory;
    }

    public function execute(Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $shipmentCollection = $objectManager->get('\Eos\Base\Model\Shipment')->getCollection();
        $shipmentCollection->addFieldToFilter('status', ['eq'=>'During payment']);
        $shipmentCollection->addFieldToFilter('customer_id', ['eq'=>$this->_customerSession->getCustomer()->getId()]);
        $shipment = $shipmentCollection->getFirstItem();


        $id = $this->_customerSession->getCustomer()->getId();

        $email = $this->_customerSession->getCustomer()->getEmail();

        /** @var $shipmentCollection ShipmentCollection */
        $shipmentCollection = $this->_shipmentCollectionFactory->create()->getShipmentOrdersDetails()->addFieldToFilter('main_table.entity_id', $shipment['entity_id']);

        $shipments = $shipmentCollection->getItems();
        $shipmentFirst = $shipmentCollection->getFirstItem();


        /** @var $parcelCollection ParcelCollection */
        $parcelCollection = $this->_parcelCollectionFactory->create()->addFieldToFilter('tracking_number', $shipmentFirst['webshop_tracking_number']);

        $parcels = $parcelCollection->getItems();

        $countParcels = 0;
        $totalWeight = 0;
        foreach ($parcels as $parcel) {

            $totalWeight += $parcel['weight'];
            $countParcels++;
        }

        $billingID =  $this->_customerSession->getCustomer()->getDefaultBilling();
        $address = $this->_addressFactory->create()->load($billingID);

        $weight = $shipment['total_weight'] > 0 ? $shipment['total_weight'] : $totalWeight;

        //The message
        $xml_open = '<?xml version="1.0" encoding="utf-8"?>
                    <Request service="ReConfrimWeightOrder" lang="en">
                    <Head>OSMS_10840</Head>
                    <Body>
                    <Order
                        is_change_order="2"
                        realweightqty="' . $weight . '"
                        rec_userno="90127712"
                        j_shippercode="AMS03A"
                        reference_no1="' . $shipmentFirst['f_shipment_id'] . '"
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
                        d_address="' . str_replace("\n"," ",$address->getData('street')) . '"
                        d_province="' . $address->getData('region') . '"
                        d_city="' . $address->getData('city') . '"
                        d_email="' . $email . '"
                        d_county=""
                        d_country="' . $address->getData('country_id') . '"
                        d_post_code="' . $address->getData('postcode') . '"
                        currency="' . $shipmentFirst['webshop_currency'] . '"
                        parcel_quantity="' . $countParcels . '"
                        pay_method="1"
                        tax_pay_type="2">';


        $xml_middle = '';
        $a = 0;
        foreach($shipments as $cargo) {

            //   if($a===0){
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
            //    }
            $a++;
        }

        $xml_close = '</Order>
            </Body>
            </Request>';

        $xml = $xml_open . $xml_middle . $xml_close;
/*  set_time_limit(0);
        header('Content-type:text/json;charset=UTF-8');
        var_dump($xml);
        die;*/

        //API Key
        $checkword = '01b2832ae2024a28';

        //base64 Encryption
        $data = base64_encode($xml);

        //Generating the validation string
        $validateStr = base64_encode(md5($xml . $checkword, false));

        //request URL
        $pmsLoginAction = 'http://osms.sf-express.com/osms/services/OrderWebService?wsdl';

        /** @var CheckoutSession $orderObj */
        $orderObj = $this->checkoutSessionFactory->create()->getLastRealOrder();


        $shipmentModel = $this->_shipment->create();
        $shipmentModel->load($shipment['entity_id']);
        $shipmentModel->setData('status', 'Payed');
        $shipmentModel->setData('m_order_id', $orderObj->getEntityId());
        try {
            $client = new \SoapClient($pmsLoginAction);
            $client->__setLocation($pmsLoginAction);
            $result=$client->sfexpressService(['data'=>$data,'validateStr'=>$validateStr,'customerCode'=>'OSMS_10840']);

            $data= json_decode( json_encode($result), true);
            $simpleXml = new \SimpleXMLElement($data['Return']);

            if(!strval($simpleXml->xpath('/Response/Head')[0]) == "ERR") {


                $resultArray['customerOrderNo'] = strval($simpleXml->xpath('/Response/Body/OrderResponse/customerOrderNo')[0]);
                $resultArray['awbNo'] = strval($simpleXml->xpath('/Response/Body/OrderResponse/mailNo')[0]);
                $resultArray['originCode'] = strval($simpleXml->xpath('/Response/Body/OrderResponse/originCode')[0]);
                $resultArray['destCode'] = strval($simpleXml->xpath('/Response/Body/OrderResponse/destCode')[0]);
                $resultArray['printUrl'] = strval($simpleXml->xpath('/Response/Body/OrderResponse/printUrl')[0]);
                $resultArray['invoiceUrl'] = strval($simpleXml->xpath('/Response/Body/OrderResponse/invoiceUrl')[0]);
                // Create Shipment Record

                $shipmentModel->setData('originCode', $resultArray['originCode']);
                $shipmentModel->setData('destCode', $resultArray['destCode']);
                $shipmentModel->setData('printUrl', $resultArray['printUrl']);
                $shipmentModel->setData('invoiceUrl', $resultArray['invoiceUrl']);

                $shipmentModel->save();
            } else {
                echo '<pre>';
                var_dump(strval($simpleXml->xpath('/Response/ERROR')[0]));
                die();
            }

        } catch (Exception $e) {


        }

    }
}
