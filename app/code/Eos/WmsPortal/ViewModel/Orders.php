<?php
namespace Eos\WmsPortal\ViewModel;

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
use Eos\Base\Model\ResourceModel\ParcelTemplate\Collection as ParcelTemplateCollection;
use Eos\Base\Model\ResourceModel\ParcelTemplate\CollectionFactory as ParcelTemplateCollectionFactory;
use Eos\Base\Model\ResourceModel\Shipment\Collection as ShipmentCollection;
use Eos\Base\Model\ResourceModel\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Eos\Base\Model\ResourceModel\Warehouse\Collection as WarehouseCollection;
use Eos\Base\Model\ResourceModel\Warehouse\CollectionFactory as WarehouseCollectionFactory;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\SessionFactory;

use Magento\Framework\View\Element\Block\ArgumentInterface;

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
     * @var ParcelTemplateCollectionFactory
     */
    protected $parcelTemplateCollectionFactory;

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

    public function __construct(
        ShipmentCollectionFactory $shipmentCollectionFactory,
        OrderCollectionFactory $orderCollectionFactory,
        OrderDetailsCollectionFactory $orderDetailsCollectionFactory,
        ParcelCollectionFactory $parcelCollectionFactory,
        ParcelTemplateCollectionFactory $parcelTemplateCollectionFactory,
        ProductGroupCollectionFactory $productGroupCollectionFactory,
        CountryCollectionFactory $countryCollectionFactory,
        WarehouseCollectionFactory $warehouseCollectionFactory,
        AddressFactory $addressFactory,
        SessionFactory $customerSession
    ) {
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderDetailsCollectionFactory = $orderDetailsCollectionFactory;
        $this->parcelCollectionFactory = $parcelCollectionFactory;
        $this->parcelTemplateCollectionFactory = $parcelTemplateCollectionFactory;
        $this->productGroupCollectionFactory = $productGroupCollectionFactory;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->warehouseCollectionFactory = $warehouseCollectionFactory;
        $this->addressFactory = $addressFactory;
        $this->customerSession = $customerSession;
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
            $shipmentCollection->addFieldToFilter('entity_id', ['eq' => $shipment]);
            return $shipmentCollection->getFirstItem();
        }
        $shipmentCollection->setOrder('main_table.created_at', 'DESC');
        $arr = [];
        $i = 0;
        foreach($shipmentCollection->getItems() as $item) {
            $arr[$i] = $item;
        }

        return $shipmentCollection->getItems();

    }

    public function getIncomingParcels() {
        /** @var $orderCollection OrderCollection */
        $orderCollection = $this->orderCollectionFactory->create()->addFieldToFilter('book_ind', 0)->addFieldToFilter('status', ['neq' => "open:init"]);

        $orderCollection->setOrder('created_at', 'DESC');
        return $orderCollection->getItems();
    }

    public function getBookedParcels() {
        /** @var $orderCollection OrderCollection */
        $orderCollection = $this->orderCollectionFactory->create()->getUnregisteredParcelInfo()->addFieldToFilter('book_ind', 1);

        $orderCollection->setOrder('created_at', 'DESC');

        return $orderCollection->getItems();
    }
    public function getOnholdParcels() {
        /** @var $orderCollection OrderCollection */
        $orderCollection = $this->orderCollectionFactory->create()->getUnregisteredParcelInfo()->addFieldToFilter('book_ind', 9);

        $orderCollection->setOrder('created_at', 'DESC');

        return $orderCollection->getItems();
    }
    public function getParcelTemplates() {
        /** @var $parcelTemplateCollection ParcelTemplateCollection */
        $parcelTemplateCollection = $this->parcelTemplateCollectionFactory->create();

        return $parcelTemplateCollection->getItems();
    }

    public function getOrdersNotConfirmed() {
        /** @var $orderCollection OrderCollection */
        $orderCollection = $this->orderCollectionFactory->create()->getRegisteredParcelInfo()->addFieldToFilter('book_ind', 1)->addFieldToFilter('shipment_id', ['eq' => 0]);

        $orderCollection->setOrder('created_at', 'DESC');

        return $orderCollection->getItems();
    }

    public function getOrdersReadyForPayment() {
        /** @var $orderCollection OrderCollection */
        $orderCollection = $this->orderCollectionFactory->create()->getRegisteredParcelInfo()->addFieldToFilter('book_ind', 1)->addFieldToFilter('main_table.status', 'Shipment created')->addFieldToFilter('eos_shipment.status', 'Parcels Measured');

        $orderCollection->setOrder('created_at', 'DESC');

        return $orderCollection->getItems();
    }
    public function getOrdersAwaitingForPayment() {
        /** @var $orderCollection OrderCollection */
        $statusImplode = implode(",", ['Ready for payment', 'During payment']);
        $orderCollection = $this->orderCollectionFactory->create()->getRegisteredParcelInfo()->addFieldToFilter('book_ind', 1)->addFieldToFilter('main_table.status', 'Shipment created')->addFieldToFilter('eos_shipment.status', ['in' => $statusImplode]);

        $orderCollection->setOrder('created_at', 'DESC');

        return $orderCollection->getItems();
    }

    public function getOrdersReadyForBatch() {
        /** @var $orderCollection OrderCollection */
        $orderCollection = $this->orderCollectionFactory->create()->getShipmentInfo()->addFieldToFilter('eos_shipment.status', 'Payed');

        $orderCollection->setOrder('created_at', 'DESC');

        return $orderCollection->getItems();
    }

}
