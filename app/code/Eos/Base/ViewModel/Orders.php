<?php
namespace Eos\Base\ViewModel;

use Eos\Base\Model\ResourceModel\Country\Collection as CountryCollection;
use Eos\Base\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Eos\Base\Model\ResourceModel\Hs\Collection as ProductGroupCollection;
use Eos\Base\Model\ResourceModel\Hs\CollectionFactory as ProductGroupCollectionFactory;
use Eos\Base\Model\ResourceModel\Order\Collection as OrderCollection;
use Eos\Base\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Eos\Base\Model\ResourceModel\OrderDetails\Collection as OrderDetailsCollection;
use Eos\Base\Model\ResourceModel\OrderDetails\CollectionFactory as OrderDetailsCollectionFactory;
use Eos\Base\Model\ResourceModel\Parcel\Collection as ParcelCollection;
use Eos\Base\Model\ResourceModel\Parcel\CollectionFactory as ParcelCollectionFactory;
use Eos\Base\Model\ResourceModel\Shipment\Collection as ShipmentCollection;
use Eos\Base\Model\ResourceModel\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Eos\Base\Model\ResourceModel\Warehouse\Collection as WarehouseCollection;
use Eos\Base\Model\ResourceModel\Warehouse\CollectionFactory as WarehouseCollectionFactory;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\SessionFactory;
use Eos\Base\Helper\CalculatePrice as HelperPrice;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Symfony\Component\Console\Helper\Helper;

class Orders implements ArgumentInterface
{
    /**
     * @var ShipmentCollectionFactory
     */
    protected $shipmentCollectionFactory;

    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var OrderDetailsCollectionFactory
     */
    protected $orderDetailsCollectionFactory;

    /**
     * @var ParcelCollectionFactory
     */
    protected $parcelCollectionFactory;

    /**
     * @var ProductGroupCollectionFactory
     */
    protected $productGroupCollectionFactory;

    /**
     * @var CountryCollectionFactory
     */
    protected $countryCollectionFactory;

    /**
     * @var WarehouseCollectionFactory
     */
    protected $warehouseCollectionFactory;

    /**
     * @var AddressCollectionFactory
     */
    protected $addressFactory;

    /**
     * @var SessionFactory
     */
    protected $customerSession;

    /**
     * @var HelperPrice
     */
    protected $helperPrice;

    public function __construct(
        ShipmentCollectionFactory $shipmentCollectionFactory,
        OrderCollectionFactory $orderCollectionFactory,
        OrderDetailsCollectionFactory $orderDetailsCollectionFactory,
        ParcelCollectionFactory $parcelCollectionFactory,
        ProductGroupCollectionFactory $productGroupCollectionFactory,
        CountryCollectionFactory $countryCollectionFactory,
        WarehouseCollectionFactory $warehouseCollectionFactory,
        AddressFactory $addressFactory,
        SessionFactory $customerSession,
        HelperPrice $helperPrice
    ) {
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderDetailsCollectionFactory = $orderDetailsCollectionFactory;
        $this->parcelCollectionFactory = $parcelCollectionFactory;
        $this->productGroupCollectionFactory = $productGroupCollectionFactory;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->warehouseCollectionFactory = $warehouseCollectionFactory;
        $this->addressFactory = $addressFactory;
        $this->customerSession = $customerSession;
        $this->helperPrice = $helperPrice;
    }

    public function getCustomer()
    {
        return $this->customerSession->create()->getCustomer();
    }

    public function getCustomerAddress()
    {
        $billingID =  $this->customerSession->create()->getCustomer()->getDefaultBilling();
        $address = $this->addressFactory->create()->load($billingID);

        return $address;
    }

    public function getShipments($status = null, $shipment = false, $customer = false)
    {
        /** @var $shipmentCollection ShipmentCollection */
        $shipmentCollection = $this->shipmentCollectionFactory->create();

        if ($customer) {
            $shipmentCollection->addFieldToFilter('customer_id', ['eq' => $this->customerSession->create()->getCustomer()->getId()]);
        }

        if ($status) {
            $shipmentCollection->addFieldToFilter('status', ['like' => "%" . $status . "%"]);
        }
        if ($shipment) {
            $shipmentCollection->addFieldToFilter('entity_id', ['eq' => $shipment]);
            return $shipmentCollection->getFirstItem();
        }
        $shipmentCollection->setOrder('created_at', 'DESC');
        return $shipmentCollection->getItems();
    }

    public function getShipmentOrders($shipmentStatus = null, $orderStatus = null, $shipment = false, $customer = false) {
        /** @var $shipmentCollection ShipmentCollection */
        $shipmentCollection = $this->shipmentCollectionFactory->create()->getShipmentOrders();

        if ($customer) {
            $shipmentCollection->addFieldToFilter('customer_id', ['eq' => $this->customerSession->create()->getCustomer()->getId()]);
        }

        if ($shipmentStatus) {
            $statusImplode = implode(",", $shipmentStatus);
            $shipmentCollection->addFieldToFilter('main_table.status', ['in' => $statusImplode]);
        }
        if ($orderStatus) {
            $statusImplode = implode(",", $orderStatus);
            $shipmentCollection->addFieldToFilter('eos_order.status', ['in' => $statusImplode]);
        }
        if ($shipment) {
            $shipmentCollection->addFieldToFilter('main_table.entity_id', ['eq' => $shipment]);
            return $shipmentCollection->getItems();
        }
        $shipmentCollection->setOrder('main_table.created_at', 'DESC');
        $arr = [];
        $i = 0;
        foreach($shipmentCollection->getItems() as $item) {
            $arr[$i] = $item;
        }

        return $shipmentCollection->getItems();

    }

    public function getShipmentIdByMagentoOrderId($m_order_id) {
        /** @var $shipmentCollection ShipmentCollection */
        $shipmentCollection = $this->shipmentCollectionFactory->create();
        $shipmentCollection->addFieldToFilter('main_table.m_order_id', ['eq' => $m_order_id]);

        return $shipmentCollection->getFirstItem()['entity_id'];

    }


    public function getShipmentsAmount($status = null, $customer = false)
    {
        $customerId = $this->customerSession->create()->getCustomer()->getId();

        /** @var $shipmentCollection ShipmentCollection */
        $shipmentCollection = $this->shipmentCollectionFactory->create()->addFieldToFilter('customer_id', $customerId);

        if ($status) {
            $statusImplode = implode(",", $status);
            $shipmentCollection->addFieldToFilter('status', ['in' => $statusImplode]);
        }
        if ($customer) {
            $shipmentCollection->addFieldToFilter('customer_id', ['eq' => $this->customerSession->create()->getCustomer()->getId()]);
        }
        return $shipmentCollection->getSize();
    }

    public function getOrders($status = null, $shipment = false, $customer = false)
    {
        /** @var $orderCollection OrderCollection */
        $orderCollection = $this->orderCollectionFactory->create();
        if ($customer) {
            $orderCollection->addFieldToFilter('customer_id', ['eq' => $this->customerSession->create()->getCustomer()->getId()]);
        }
        if ($status) {
           // foreach ($status as $key => $value) {
               // if ($key != "form_key" && $value != "") {
                    //$orderCollection->addFieldToFilter('status', ['like' => "%" . $value . "%"]);
                    $statusImplode = implode(",", $status);
                    $orderCollection->addFieldToFilter('main_table.status', ['in' => $statusImplode]);
               // }
           // }
        }
        if ($shipment) {
            if($shipment < 0) {
                $orderCollection->addFieldToFilter('shipment_id', ['eq' => 0]);
            } else {
                $orderCollection->addFieldToFilter('shipment_id', ['eq' => $shipment]);
            }

        }

        $orderCollection->setOrder('created_at', 'DESC');
        return $orderCollection->getItems();
    }
    public function getOrder($order_id)
    {
        /** @var $orderCollection OrderCollection */
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter('entity_id', ['eq' => $order_id]);
        return $orderCollection->getFirstItem();
    }

    public function getOrdersAmount($status = null, $customer = null, $shipmentId = null)
    {
        /** @var $orderCollection OrderCollection */
        $orderCollection = $this->orderCollectionFactory->create();

        if ($status) {
            $statusImplode = implode(",", $status);
            $orderCollection->addFieldToFilter('status', ['in' => $statusImplode]);
        }

        if ($customer) {
            $orderCollection->addFieldToFilter('customer_id', ['eq' => $this->customerSession->create()->getCustomer()->getId()]);
        }
        if ($shipmentId) {
            $orderCollection->addFieldToFilter('shipment_id', ['eq' => $shipmentId]);
        }

        return $orderCollection->getSize();
    }

    public function getOrderDetails($orderId)
    {
        /** @var $orderDetailsCollection OrderDetailsCollection */
        $orderDetailsCollection = $this->orderDetailsCollectionFactory->create();
        // ->getOrderInfo() REMOVED+

        $orderDetailsCollection->addFieldToFilter('order_id', ['eq' => $orderId]);

        return $orderDetailsCollection->getItems();
    }

    public function getOrderDetailsAmount($orderId)
    {
        /** @var $orderDetailsCollection OrderDetalsCollection */
        $orderDetailsCollection = $this->orderDetailsCollectionFactory->create();

        $orderDetailsCollection->addFieldToFilter('order_id', ['eq' => $orderId]);

        return $orderDetailsCollection->getSize();
    }

    public function getParcels($trackingNr = false)
    {
        /** @var $parcelCollection ParcelCollection */
        $parcelCollection = $this->parcelCollectionFactory->create();

        $parcelCollection->addFieldToFilter('tracking_number', ['eq' => $trackingNr]);

        return $parcelCollection->getItems();
    }

    public function getOrderDetailsWithTaxes($orderId)
    {
        /** @var $orderDetailsCollection OrderDetailsCollection */
        $orderDetailsCollection = $this->orderDetailsCollectionFactory->create()->addFieldToFilter('order_id', ['eq' => $orderId]);

        $orderDetailsCollection->getSelect()->join(
            ['eos_hs_china'=>$orderDetailsCollection->getTable('eos_hs_china')],
            'main_table.product_tax_nr = eos_hs_china.product_tax_nr'
        );

        return $orderDetailsCollection->getItems();
    }

    public function getOrderDetailsWithHs($orderId)
    {
        /** @var $orderDetailsCollection OrderDetailsCollection */
        $orderDetailsCollection = $this->orderDetailsCollectionFactory->create()->addFieldToFilter('order_id', ['eq' => $orderId]);
        $orderDetailsCollection->getSelect()->columns(array('orderDetail_id' => 'entity_id', '*'));


        $orderDetailsCollection->getSelect()->joinLeft(
            ['eos_hs'=>$orderDetailsCollection->getTable('eos_hs')],
            'main_table.product_tax_nr = eos_hs.product_tax_nr'

        );

        /*$orderDetailsCollection->getSelect()->join(
            ['eos_hs_china'=>$orderDetailsCollection->getTable('eos_hs_china')],
            'main_table.product_tax_nr = eos_hs_china.product_tax_nr'
        );*/

        $orderDetailsCollection->getSelect()->joinLeft(
            ['eos_hs_product'=>$orderDetailsCollection->getTable('eos_hs_product')],
            'main_table.product_tax_nr = eos_hs_product.product_tax_nr AND lang = "en"',
            ['entity_id','product_tax_nr','hs_description'  => 'eos_hs_product.product_title']
        );

        $orderDetailsCollection->getSelect()->joinLeft(
            ['eos_hs_category'=>$orderDetailsCollection->getTable('eos_hs_category')],
            'eos_hs.category_id = eos_hs_category.category_id'
        );

        $orderDetailsCollection->getSelect()->joinLeft(
            ['eos_hs_subcategory'=>$orderDetailsCollection->getTable('eos_hs_subcategory')],
            'eos_hs.subcategory_id = eos_hs_subcategory.subcategory_id AND eos_hs_subcategory.category_id = eos_hs_category.category_id'
        );




        return $orderDetailsCollection->getItems();
    }

    public function countParcelsShipment($shipment)
    {
        /** @var $orderCollection OrderCollection */
        $orderCollection = $this->orderCollectionFactory->create();

        if ($shipment) {
            $orderCollection->addFieldToFilter('shipment_id', ['eq' => $shipment]);
        }
        $orderCollection->getSelect()->join(
            ['eos_parcel'=>$orderCollection->getTable('eos_parcel')],
            'main_table.webshop_tracking_number = eos_parcel.tracking_number'
        );

        $count = $orderCollection->getSize();

        return $count;
    }

    public function totalParcelWeightShipment($shipment)
    {
        /** @var $orderCollection OrderCollection */
        $orderCollection = $this->orderCollectionFactory->create();

        if ($shipment) {
            $orderCollection->addFieldToFilter('shipment_id', ['eq' => $shipment]);
        }
        $orderCollection->getSelect()->join(
            ['eos_parcel'=>$orderCollection->getTable('eos_parcel')],
            'main_table.webshop_tracking_number = eos_parcel.tracking_number'
        );

        $items = $orderCollection->getItems();
        $total['weight'] = 0;
        $total['price'] = 15;
        foreach($items as $item) {
            $formula = ($item['length'] * $item['width'] * $item['height']) / 5000;
            $total['weight'] += $formula > $item['weight'] ? $formula : $item['weight'];
            $total['price'] += $total['weight'] * 2.70;
        }

        return $total;
    }

    public function getProductGroup()
    {
        /** @var $productGroupCollection ProductGroupCollection */
        $productGroupCollection = $this->productGroupCollectionFactory->create()->getProducts();

        return $productGroupCollection->getItems();
    }

    public function getCategories()
    {
        /** @var $productGroupCollection ProductGroupCollection */
        $productGroupCollection = $this->productGroupCollectionFactory->create()->getCategories();
        $itemArray = [];
        $i = 0;
        foreach ($productGroupCollection->getItems() as $item) {
            if ($i!==0) {
                if ($item['category_title'] !== $itemArray[$i-1]['category_title']) {
                    $itemArray[$i]['category_id'] = $item['category_id'];
                    $itemArray[$i]['category_title'] = $item['category_title'];
                    $i++;
                }
            } else {
                $itemArray[$i]['category_id'] = $item['category_id'];
                $itemArray[$i]['category_title'] = $item['category_title'];
                $i++;
            }
        }


        return $itemArray;
    }
    public function getSubCategories()
    {
        /** @var $productGroupCollection ProductGroupCollection */
        $productGroupCollection = $this->productGroupCollectionFactory->create()->getSubCategories();
        $itemArray = [];
        $i = 0;
        foreach ($productGroupCollection->getItems() as $item) {
            if ($i!==0) {
                if ($item['subcategory_title'] !== $itemArray[$i-1]['subcategory_title']) {
                    $itemArray[$i]['category_id'] = $item['category_id'];
                    $itemArray[$i]['subcategory_id'] = $item['subcategory_id'];
                    $itemArray[$i]['subcategory_title'] = $item['subcategory_title'];
                    $i++;
                }
            } else {
                $itemArray[$i]['category_id'] = $item['category_id'];
                $itemArray[$i]['subcategory_id'] = $item['subcategory_id'];
                $itemArray[$i]['subcategory_title'] = $item['subcategory_title'];
                $i++;
            }
       }

        return $itemArray;
    }

    public function getSubCategoriesCount()
    {
        /** @var $productGroupCollection ProductGroupCollection */
        $productGroupCollection = $this->productGroupCollectionFactory->create()->getSubCategoriesRight()->getSize();
        /*$itemArray = [];
        $i = 0;
        foreach ($productGroupCollection->getItems() as $item) {
            if ($i!==0) {
                if ($item['subcategory_title'] !== $itemArray[$i-1]['subcategory_title']) {
                    $itemArray[$i]['category_id'] = $item['category_id'];
                    $itemArray[$i]['subcategory_id'] = $item['subcategory_id'];
                    $itemArray[$i]['subcategory_title'] = $item['subcategory_title'];
                    $i++;
                }
            } else {
                $itemArray[$i]['category_id'] = $item['category_id'];
                $itemArray[$i]['subcategory_id'] = $item['subcategory_id'];
                $itemArray[$i]['subcategory_title'] = $item['subcategory_title'];
                $i++;
            }
        }*/

        return $productGroupCollection;
    }

    public function getCountry($id = null, $code = null)
    {

        /** @var $countryCollection CountryCollection */
        $countryCollection = $this->countryCollectionFactory->create();

        if ($id) {
            $countryCollection->addFieldToFilter('country_id', ['like' => $id]);
        }

        if ($code) {
            $countryCollection->addFieldToFilter('country_code', ['like' => $code]);
        }

        return $countryCollection->getFirstItem();
    }

    public function getCountries($type, $lang = 'en')
    {
        /** @var $countryCollection CountryCollection */
        $countryCollection = $this->countryCollectionFactory->create();
        $countryCollection->addFieldToFilter('country_type', ['like' => $type]);
        $countryCollection->addFieldToFilter('country_lang', ['like' => $lang]);
        return $countryCollection->getItems();
    }

    public function getWarehouses($id = null)
    {
        /** @var $warehouseCollection WarehouseCollection */
        $warehouseCollection = $this->warehouseCollectionFactory->create();

        if ($id) {
            $warehouseCollection->addFieldToFilter('entity_id', ['eq' => $id]);

            return $warehouseCollection->getFirstItem();
        }

        return $warehouseCollection->getItems();
    }
    public function getWarehouse($id)
    {
        /** @var $warehouseCollection WarehouseCollection */
        $warehouseCollection = $this->warehouseCollectionFactory->create();
        $warehouseCollection->addFieldToFilter('entity_id', ['eq' => $id]);

        return $warehouseCollection->getFirstItem();

    }




    public function htmlToDoItem($card_header, $cta_link, $cta_text, $card_body) {

        return "<div class=\"row col-12 card-option-holder mb-40\">
                <div class=\"col-12\">
                    <div class=\"card card-option card-option-active\">
                        <div class=\"card-header\">
                            <div class=\"row col-12 p-4\">

                                <div class=\"col-8 text-left\">
                                    <span>" . $card_header . "</span>
                                </div>
                                <div class=\"col-4 text-right\">
                                    <a href='" .  $cta_link . "' class=\"btn btn-tertiary btn-small text-dark\">" .  $cta_text . "
                                        <svg width='26' height='11' viewBox='0 0 26 11' fill='none' xmlns='http://www.w3.org/2000/svg'>
                                            <path d='M20.5 1L25 5.5L20.5 10' stroke='currentColor' stroke-width='1.3' stroke-linecap='round' stroke-linejoin='round' />
                                            <path d='M7 5.5H25' stroke='currentColor' stroke-width='1.3' stroke-linecap='round' stroke-linejoin='round' />
                                        </svg>
                                    </a>
                                </div>
                            </div>

                        </div>
                        <div class=\"card-body\">
                            <span>" .  $card_body . "</span>
                        </div>
                    </div>
                </div>
            </div>";
    }

    public function getShipmentPrice($shipment_id, $total = false) {
        return $this->helperPrice->calculatePrice($shipment_id, $total);
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

    public function getEmptyShipmentMessage()
    {
        return __('You have placed no shipments.');
    }
    public function getEmptyCreateShipmentMessage()
    {
        return __('There still is no parcel information uploaded yet. Please do ');
    }
    public function getEmptyOrdersMessage()
    {
        return __('You have placed no Orders yet.');
    }

    public function getEmptyUploadIdMessage()
    {
        return __('You have placed no Orders yet. Please create a shipment before uploading ID card first.');
    }
}
