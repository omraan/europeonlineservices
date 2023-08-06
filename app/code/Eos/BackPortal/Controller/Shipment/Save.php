<?php

namespace Eos\BackPortal\Controller\Shipment;

use Eos\Base\Model\OrderFactory;
use Eos\Base\Model\ResourceModel\Order;
use Eos\Base\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Eos\Base\Model\ResourceModel\Parcel\Collection as ParcelCollection;
use Eos\Base\Model\ResourceModel\Parcel\CollectionFactory as ParcelCollectionFactory;
use Eos\Base\Model\ResourceModel\Shipment\Collection as ShipmentCollection;
use Eos\Base\Model\ResourceModel\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Eos\Base\Model\ShipmentFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\SessionFactory as Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Webapi\Soap\ClientFactory;

class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var OrderFactory
     */
    protected $_order;

    /**
     * @var ShipmentFactory
     */
    protected $_shipment;

    /**
     * @var FormKey
     */

    protected $_formKey;

    /**
     * @var Cart
     */

    protected $_cart;

    /**
     * @var ProductFactory
     */

    protected $_productFactory;

    /**
     * @var Product
     */

    protected $_product;

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

    public function __construct(
        Context $context,
        Session $customerSession,
        OrderFactory $order,
        ShipmentFactory $shipment,
        FormKey $formKey,
        Cart $cart,
        ProductFactory $productFactory,
        Product $product,
        OrderCollectionFactory $orderCollectionFactory,
        AddressFactory $_addressFactory,
        ShipmentCollectionFactory $shipmentCollectionFactory,
        ParcelCollectionFactory $parcelCollectionFactory,
        ClientFactory $soapClientFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_order = $order;
        $this->_shipment = $shipment;
        $this->_cart = $cart;
        $this->_productFactory = $productFactory;
        $this->_formKey = $formKey;
        $this->_product = $product;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->_parcelCollectionFactory = $parcelCollectionFactory;
        $this->_addressFactory = $_addressFactory;
        $this->soapClientFactory = $soapClientFactory;
        parent::__construct($context);
    }
    public function execute()
    {
        $customer = $this->_customerSession->create();
        $customerId =  $customer->getCustomer()->getId();
        $resultRedirect = $this->resultRedirectFactory->create();

        // Check if user is logged-in
        if ($customerId > 0) {
            // Params are order_{orderID} + Form_key
            $post = $this->getRequest()->getParams();

            // Create Shipment Record
            $shipmentModel = $this->_shipment->create();
            $shipmentModel->setData('status', 'open');
            $shipmentModel->setData('customer_id', $customerId);
            $shipmentModel->save();

            $shipmentId = $shipmentModel->getId();

            $shipmentModel->load($shipmentId)->setData('f_shipment_id', "EOS" . str_pad($shipmentId, 10, "0", STR_PAD_LEFT) . '_test0881');
            $shipmentModel->save();

            $orderModel = $this->_order->create();
            $orderList = [];

            // Loop through all order_{orderID}
            $i = 0;

            $string = "order";

            foreach ($post as $key => $value) {
                if (str_starts_with($key, $string)) {
                    $orderList[$i] = explode("_", $key)[1];

                    $orderModel->load($orderList[$i]);
                    $orderModel->setData('shipment_id', $shipmentId);
                    $orderModel->setData('status', 'Shipment created');
                    $orderModel->save();
                }
                $i++;
            }


            $email = $this->_customerSession->create()->getCustomer()->getEmail();

            /** @var $shipmentCollection ShipmentCollection */
            $shipmentCollection = $this->_shipmentCollectionFactory->create()->getShipmentOrdersDetails()->addFieldToFilter('main_table.entity_id', $shipmentId);

            $shipments = $shipmentCollection->getItems();
            $shipmentFirst = $shipmentCollection->getFirstItem();

            /** @var $parcelCollection ParcelCollection */
            $parcelCollection = $this->_parcelCollectionFactory->create()->addFieldToFilter('tracking_number', $shipmentFirst['webshop_tracking_number']);

            $parcels = $parcelCollection->getItems();

            $countParcels = 0;

            foreach ($parcels as $parcel) {
                $parcelWeight[$countParcels] = $parcel['weight'];
                $countParcels++;
            }
            $countParcels = $countParcels == 0 ? 1 : $countParcels;

            $billingID =  $customer->getCustomer()->getDefaultBilling();
            $address = $this->_addressFactory->create()->load($billingID);


            $active = 'prod';

          //  $url['test'] = "http://osms.sit.sf-express.com:2080/osms/services/OrderWebService?wsdl";
            $url['test'] = 'http://osms.sit.sf-express.com:2080/osms/services/OrderWebService?wsdl';
            $url['prod'] = "http://osms.sf-express.com/osms/services/OrderWebService?wsdl";

            $checkword['test'] = "fc34c561a34f";
            $checkword['prod'] = "01b2832ae2024a28";

            $account['test'] = 'OSMS_1';
            $account['prod'] = 'OSMS_10840';

            //The message
            $xml_open = '<?xml version="1.0" encoding="utf-8"?>
                    <Request service="ReConfrimWeightOrder" lang="en">
                    <Head>' . $account[$active] . '</Head>
                    <Body>
                    <Order
                        is_change_order="1"
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
            $a = 0;


            // Since eos_hs_product.product_title and eos_order_details.product_title have the same column name, in ShipmentsCollection, eos_order_details.product_title has been changed to product_name
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
                $a++;
            }

            $xml_close = '</Order>
            </Body>
            </Request>';

            $xml = $xml_open . $xml_middle . $xml_close;

            //base64 Encryption
            $data = base64_encode($xml);

            //Generating the validation string
            $validateStr = base64_encode(md5($xml . $checkword[$active], false));

            try {
                $client = new \SoapClient($url[$active]);
                $client->__setLocation($url[$active]);
                $result=$client->sfexpressService(['data'=>$data,'validateStr'=>$validateStr,'customerCode'=> $account[$active]]);

                $data= json_decode(json_encode($result), true);
                $simpleXml = new \SimpleXMLElement($data['Return']);

                $resultArray['customerOrderNo'] = strval($simpleXml->xpath('/Response/Body/OrderResponse/customerOrderNo')[0]);
                $resultArray['awbNo'] = strval($simpleXml->xpath('/Response/Body/OrderResponse/mailNo')[0]);
                $resultArray['originCode'] = strval($simpleXml->xpath('/Response/Body/OrderResponse/originCode')[0]);
                $resultArray['destCode'] = strval($simpleXml->xpath('/Response/Body/OrderResponse/destCode')[0]);
                $resultArray['printUrl'] = strval($simpleXml->xpath('/Response/Body/OrderResponse/printUrl')[0]);
                $resultArray['invoiceUrl'] = strval($simpleXml->xpath('/Response/Body/OrderResponse/invoiceUrl')[0]);

                $shipmentModel = $this->_shipment->create()->load($shipmentId)->setData('awb_code', $resultArray['awbNo']);
                $shipmentModel->save();
            } catch (Exception $e) {
                exit($e);
            }

            $resultRedirect->setPath('portal/uploadid/create', ['shipment' => $shipmentId]);
        } else {
            $resultRedirect->setPath('customer/account/login');
        }
        return $resultRedirect;
    }
}
