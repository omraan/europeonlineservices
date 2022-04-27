<?php
namespace Eos\Base\Helper;

use Eos\Base\Helper\Email;
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
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\UrlInterface;

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
        $this->_responseFactory = $responseFactory;
        $this->_url = $url;
        parent::__construct($context);
    }

    public function ExportJson($customerId, $shipment_id = null, $orders = null, $correction = false)
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



    }

}
