<?php
namespace Eos\Base\Helper;

use Cassandra\Date;
use Eos\Base\Helper\Email;
use Eos\Base\Model\ResourceModel\Order\Collection as OrderCollection;
use Eos\Base\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Eos\Base\Model\ResourceModel\Parcel\Collection as ParcelCollection;
use Eos\Base\Model\ResourceModel\Parcel\CollectionFactory as ParcelCollectionFactory;
use Eos\Base\Model\ResourceModel\Shipment\Collection as ShipmentCollection;
use Eos\Base\Model\ResourceModel\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Eos\Base\Model\ResourceModel\Batch\Collection as BatchCollection;
use Eos\Base\Model\ResourceModel\Batch\CollectionFactory as BatchCollectionFactory;
use Eos\Base\Model\ShipmentFactory;
use Eos\Base\Model\OrderFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\UrlInterface;
use function PHPUnit\Framework\isNull;
use Eos\Base\Model\ResourceModel\ChineseAddress\CollectionFactory as ChineseAddressCollectionFactory;

class ExportBatch extends AbstractHelper
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
     * @var BatchCollectionFactory
     */
    protected $_batchCollectionFactory;

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

    /**
     * @var DateTime
     */

    protected $_dateTime;
    /**
     * @var ChineseAddressCollectionFactory
     */
    protected $_chineseAddressCollectionFactory;


    public function __construct(
        Context         $context,
        ShipmentFactory $shipment,
        OrderFactory $order,
        Product $product,
        AddressFactory $_addressFactory,
        ShipmentCollectionFactory $shipmentCollectionFactory,
        BatchCollectionFactory $batchCollectionFactory,
        OrderCollectionFactory $orderCollectionFactory,
        ParcelCollectionFactory $parcelCollectionFactory,
        CustomerRepositoryInterface $customerRepositoryInterface,
        ResponseFactory $responseFactory,
        UrlInterface $url,
        DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        ChineseAddressCollectionFactory $chineseAddressCollectionFactory
    )
    {
        $this->_shipment = $shipment;
        $this->_order = $order;
        $this->_product = $product;
        $this->_addressFactory = $_addressFactory;
        $this->_shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->_batchCollectionFactory = $batchCollectionFactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_parcelCollectionFactory = $parcelCollectionFactory;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_responseFactory = $responseFactory;
        $this->_url = $url;
        $this->_dateTime = $dateTime;
        $this->timezone = $timezone;
        $this->_chineseAddressCollectionFactory = $chineseAddressCollectionFactory;
        parent::__construct($context);
    }

    public function ExportJson($batchId, $shipment_id = null, $orders = null, $correction = false)
    {
        /** @var $batchCollection BatchCollection */
        $batchCollection = $this->_batchCollectionFactory->create()->getBatches()->addFieldToFilter('main_table.entity_id', $batchId)->getItems();

        foreach($batchCollection as $batchItem) {
            /** @var $shipmentCollection ShipmentCollection */
            $shipmentCollection = $this->_shipmentCollectionFactory->create()->getShipmentOrders()->addFieldToFilter('main_table.awb_code', $batchItem['awb_code']);
            $shipments = $shipmentCollection->getItems();
            $shipmentFirst = $shipmentCollection->getFirstItem();

            $customer = $this->_customerRepositoryInterface->getById($shipmentFirst['customer_id']);
            $email = $customer->getEmail();

            /** @var $address Address */
            $address = $this->_addressFactory->create()->load($customer->getDefaultBilling());
            $chineseAddress = $this->_chineseAddressCollectionFactory->create()->addFieldToFilter('city', ['in' => $address->getData('city')])->toArray()['items'][0];

            $customerName = $customer->getCustomAttribute('customer_name_en')->getValue() !== null ? $customer->getCustomAttribute('customer_name_en')->getValue() : $customer->getFirstname() . " " . $customer->getLastname();


            $created_at = $this->timezone->date(new \DateTime($batchItem['created_at']))->format('Y-m-d') . "T" . $this->timezone->date(new \DateTime($batchItem['created_at']))->format('H:i:s+2:00');

            foreach($shipments as $shipment) {

                $json = [
                    'mawb' => $batchItem['mawb_code'],
                    'hawb' => '',
                    'flight_no' => $batchItem['mawb_code'],
                    'create_time' => $created_at
                ];
                /** @var $parcelCollection ParcelCollection */
                $parcelFirst = $this->_parcelCollectionFactory->create()->addFieldToFilter('tracking_number', $shipmentFirst['webshop_tracking_number'])->getFirstItem();

                $json += [
                    "package_width" => $parcelFirst['width'],
                    "package_height" => $parcelFirst['height'],
                    "package_length" => $parcelFirst['length'],
                    "dimension_unit" => "CM",
                    "gross_weight" => $parcelFirst['weight'],
                    "weight_unit" => "KG"
                ];

                $json += [
                    'order_number' => $shipment['webshop_order_nr'],
                    'tracking_number' => $shipment['webshop_tracking_number'],
                    'declared_value' => $shipment['webshop_order_total_price_gross'],
                    "freight_cost" => 0,
                    "currency" => "EUR",
                    "weight_unit" => "KG",
                    "seller" => [
                        "contact" => "Europe Online Services BV",
                        "address" => "Dennenlaan 9",
                        "city" => "Sint-Michielsgestel",
                        "province" => "Noord-Brabant",
                        "post_code" => "5271RE",
                        "country" => "NL",
                        "phone" => "31682274151",
                        "tax_id" => "",
                    ],
                    "buyer" => [
                        "contact" => $customerName,
                        "address" => $address['chinese_address_street'],
                        "city" => $chineseAddress['city_en'],
                        "province" => $chineseAddress['province_en'],
                        "post_code" => $address['postcode'],
                        "country" => "CN",
                        "phone" => "",
                        "tax_id" => "",
                    ]
                ];
                $orderArray = [];
                foreach($this->_shipmentCollectionFactory->create()->getShipmentOrders()->addFieldToFilter('eos_order.entity_id', $shipment['order_id']) as $order) {

                    $parcel = $this->_parcelCollectionFactory->create()->addFieldToFilter('tracking_number', $order['webshop_tracking_number'])->getFirstItem();
                    $orderArray += [
                        "item_code" => "",
                        "name" => $order['product_name'],
                        "currency" => "EUR",
                        "unit_price" => $order['product_price_gross'],
                        "quantity" => $order['product_amount'],
                        "net_weight" => $parcel['weight'],
                        "country_of_origin" => "NL",
                        "unit" => "EA",
                        "hs_code" => $order['hs_code'],
                        "hs_description" => $order['product_title']
                    ];
                }

            }
            $json += [
                "items"=> $orderArray
            ];

            echo '<pre>';
            var_dump(json_encode($json,JSON_PRETTY_PRINT));
            die();


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

        }














    }

}
