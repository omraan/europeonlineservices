<?php

namespace Eos\Base\ViewModel;

use Eos\Base\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Customer\Model\SessionFactory;

class OrderViewModel extends BaseViewModel
{
    private $orderCollectionFactory;

    protected $orderCollection;

    public function __construct(
        SessionFactory $customerSession,
        OrderCollectionFactory $orderCollectionFactory
    ) {
        parent::__construct($customerSession);
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    public function getOrders()
    {
        $this->orderCollection = $this->orderCollectionFactory->create();
        return $this;
    }

    public function getOrderDetails($id)
    {
        $this->orderCollection = $this->orderCollectionFactory->create();
        $this->orderCollection->getOrdersDetails();
        $this->orderCollection->addFieldToFilter('order_id', ['eq' => $id]);
        return $this;
    }

    public function getOrderParcels($id = null)
    {
        $this->orderCollection = $this->orderCollectionFactory->create();
        $this->orderCollection->getSelect()->join(
            ['eos_parcel' => $this->orderCollection->getTable('eos_parcel')],
            'main_table.webshop_tracking_number = eos_parcel.tracking_number'
        );

        if ($id) {
            $this->orderCollection->addFieldToFilter('main_table.entity_id', ['eq' => $id]);
        }

        return $this;
    }

    public function withJoinParcels()
    {
        $this->orderCollection->getSelect()->join(
            ['eos_parcel' => $this->orderCollection->getTable('eos_parcel')],
            'main_table.webshop_tracking_number = eos_parcel.tracking_number'
        );
        return $this;
    }

    public function filterById($id)
    {
        $this->orderCollection->addFieldToFilter('entity_id', ['eq' => $id]);
        return $this;
    }

    public function filterByCustomer($customerId = null)
    {
        $customerId = $customerId ? $customerId : $this->customerSession->create()->getCustomer()->getId();
        $this->orderCollection->addFieldToFilter('customer_id', ['eq' => $customerId]);
        return $this;
    }

    public function filterByShipmentId($shipmentId)
    {
        $this->orderCollection->addFieldToFilter('shipment_id', ['eq' => $shipmentId]);
        return $this;
    }

    public function filterByStatus($status)
    {
        if (!empty($status)) {
            $statusImplode = implode(",", $status);
            $this->orderCollection->addFieldToFilter('status', ['in' => $statusImplode]);
        }
        return $this;
    }

    public function sortOrders()
    {
        $this->orderCollection->setOrder('main_table.created_at', 'DESC');
    }

    public function getFirstItem()
    {
        return $this->orderCollection->getFirstItem();
    }

    public function getItems()
    {
        $this->sortOrders();
        return $this->orderCollection->getItems();
    }

    public function getSize()
    {
        return $this->orderCollection->getSize();
    }

    public function getQuery()
    {
        return $this->orderCollection->getSelect()->__toString();
    }

    public function getEmptyOrdersMessage()
    {
        return __('You have placed no Orders yet.');
    }
}