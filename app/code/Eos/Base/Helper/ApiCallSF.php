<?php
namespace Eos\Base\Helper;

use Eos\Base\Model\ResourceModel\Order\Collection as OrderCollection;
use Eos\Base\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Eos\Base\Model\ResourceModel\Parcel\Collection as ParcelCollection;
use Eos\Base\Model\ResourceModel\Parcel\CollectionFactory as ParcelCollectionFactory;
use Eos\Base\Model\ResourceModel\Shipment\Collection as ShipmentCollection;
use Eos\Base\Model\ResourceModel\Shipment\CollectionFactory as ShipmentCollectionFactory;
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

class ApiCallSF extends AbstractHelper
{
    /**
     * @var ShipmentFactory
     */
    protected $_shipment;

    /**
     * @var OrderFactory
     */
    protected $_order;

    /**
     * @var ProductFactory
     */

    protected $_productFactory;

    /**
     * @var Product
     */

    protected $_product;

    /**
     * @var AddressFactory
     */

    protected $_addressFactory;

    /**
     * @var OrderCollectionFactory
     */

    protected $_orderCollectionFactory;

    /**
     * @var ShipmentCollectionFactory
     */
    protected $_shipmentCollectionFactory;

    /**
     * @var ParcelCollectionFactory
     */
    protected $_parcelCollectionFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepositoryInterface;

    /** @var Email */

    protected $helperEmail;

    /**
     * @var ResponseFactory
     */
    protected $_responseFactory;

    /**
     * @var UrlInterface
     */

    protected $_url;

    public function __construct(
        Context         $context,
        ShipmentFactory $shipment,
        OrderFactory $order,
        Product $product,
        AddressFactory $_addressFactory,
        ShipmentCollectionFactory $shipmentCollectionFactory,
        OrderCollectionFactory $orderCollectionFactory,
        ParcelCollectionFactory $parcelCollectionFactory,
        CustomerRepositoryInterface $customerRepositoryInterface,
        Email $helperEmail,
        ResponseFactory $responseFactory,
        UrlInterface $url
    )
    {
        $this->_shipment = $shipment;
        $this->_order = $order;
        $this->_product = $product;
        $this->_addressFactory = $_addressFactory;
        $this->_shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_parcelCollectionFactory = $parcelCollectionFactory;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->helperEmail = $helperEmail;
        $this->_responseFactory = $responseFactory;
        $this->_url = $url;
        parent::__construct($context);
    }

    public function ReConfrimWeightOrder($customerId, $shipment_id = null, $orders = null, $correction = false)
    {
        if(!isset($shipment_id)) {
            // Create Shipment Record
            $shipmentModel = $this->_shipment->create();
            $shipmentModel->setData('status', 'open');
            $shipmentModel->setData('customer_id', $customerId);
            $shipmentModel->save();

            $shipmentId = $shipmentModel->getId();

            $shipmentModel->load($shipmentId)->setData('f_shipment_id', "EOS" . str_pad($shipmentId, 10, "0", STR_PAD_LEFT) . '_test0312');
            $shipmentModel->save();
        } else {

            // This is done in order to check later whether $shipment_id already exist before this function call.
            $shipmentId = $shipment_id;

        }

        if(isset($orders)) {
            foreach ($orders as $order) {
                $orderModel = $this->_order->create();
                $orderModel->load($order['entity_id']);
                $orderModel->setData('shipment_id', $shipmentId);
                $orderModel->setData('status', 'Shipment created');
                $orderModel->save();
            }
        }

        $customer = $this->_customerRepositoryInterface->getById($customerId);
        $email = $customer->getEmail();
        $address = $this->_addressFactory->create()->load($customer->getDefaultBilling());

        /** @var $shipmentCollection ShipmentCollection */
        $shipmentCollection = $this->_shipmentCollectionFactory->create()->getShipmentOrdersDetails()->addFieldToFilter('main_table.entity_id', $shipmentId);

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
                        d_address="' . str_replace("\n"," ", $address->getData('street')) . '"
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
        $result=$client->sfexpressService(['data'=>$data,'validateStr'=>$validateStr,'customerCode'=>'OSMS_10840']);

        $data= json_decode(json_encode($result), true);
        $simpleXml = new \SimpleXMLElement($data['Return']);
        if(strval($simpleXml->xpath('/Response/Head')[0]) == "OK") {

            $resultArray['customerOrderNo'] = strval($simpleXml->xpath('/Response/Body/OrderResponse/customerOrderNo')[0]);
            $resultArray['awbNo'] = strval($simpleXml->xpath('/Response/Body/OrderResponse/mailNo')[0]);
            $resultArray['originCode'] = strval($simpleXml->xpath('/Response/Body/OrderResponse/originCode')[0]);
            $resultArray['destCode'] = strval($simpleXml->xpath('/Response/Body/OrderResponse/destCode')[0]);
            $resultArray['printUrl'] = strval($simpleXml->xpath('/Response/Body/OrderResponse/printUrl')[0]);
            $resultArray['invoiceUrl'] = strval($simpleXml->xpath('/Response/Body/OrderResponse/invoiceUrl')[0]);

            $shipmentModel = $this->_shipment->create()->load($shipmentId)->setData('awb_code', $resultArray['awbNo']);
            $shipmentModel->setData('originCode', $resultArray['originCode']);
            $shipmentModel->setData('destCode', $resultArray['destCode']);
            $shipmentModel->setData('printUrl', $resultArray['printUrl']);
            $shipmentModel->setData('invoiceUrl', $resultArray['invoiceUrl']);
            $shipmentModel->save();
            $return['success'] = true;

        } else {
            $message  = strval($simpleXml->xpath('/Response/ERROR')[0]);
            $CustomRedirectionUrl = $this->_url->getUrl('portal/shipment/error',['message'=>$message]);
            $this->_responseFactory->create()->setRedirect($CustomRedirectionUrl)->sendResponse();
            $templateVars['type_error'] = isset($shipment_id) ? "SF API Call after payment" : "SF First API Call";
            $templateVars['message'] = $message;
            $templateVars['customer_id'] = $customerId;
            $templateVars['customer_email'] = $email;
            $this->helperEmail->sendErrorEmail(8,$templateVars);
            $return['success'] = false;

        }

        $return['shipment_id'] = $shipmentId;
        return $return;
    }

}
