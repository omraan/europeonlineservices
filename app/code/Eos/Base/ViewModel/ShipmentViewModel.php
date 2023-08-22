<?php

namespace Eos\Base\ViewModel;

use Eos\Base\Helper\CalculatePrice as HelperPrice;
use Eos\Base\Model\ResourceModel\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Eos\Base\Helper\ApiCallSF as helperApiCallSF;
use Magento\Customer\Model\SessionFactory;

class ShipmentViewModel extends BaseViewModel
{
    private $shipmentCollectionFactory;
    private $helperPrice;
    private $helperApiCallSF;
    protected $shipmentCollection;

    public function __construct(
        SessionFactory $customerSession,
        ShipmentCollectionFactory $shipmentCollectionFactory,
        HelperPrice $helperPrice,
        helperApiCallSF $helperApiCallSF
    ) {
        parent::__construct($customerSession);
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->helperPrice = $helperPrice;
        $this->helperApiCallSF = $helperApiCallSF;
    }

    public function getShipments()
    {
        if (!$this->shipmentCollection) {
            $this->shipmentCollection = $this->shipmentCollectionFactory->create();
        }
        return $this;
    }

    public function getShipmentShipments()
    {
        if (!$this->shipmentCollection) {
            $this->shipmentCollection = $this->shipmentCollectionFactory->create()->getShipmentShipments();
        }
        return $this;

    }

    public function filterById($id)
    {
        return $this->shipmentCollection->addFieldToFilter('main_table.entity_id', ['eq' => $id]);
    }

    public function filterByCustomer($customerId = null)
    {
        $customerId = $customerId ? $customerId : $this->customerSession->create()->getCustomer()->getId();
        return $this->shipmentCollection->addFieldToFilter('main_table.customer_id', ['eq' => $customerId]);
    }

    public function filterByStatus($status)
    {
        $statusImplode = implode(",", $status);
        return $this->shipmentCollection->addFieldToFilter('main_table.status', ['in' => $statusImplode]);
    }
    public function getShipmentByMagentoShipmentId($mShipmentId)
    {
        return $this->shipmentCollection->addFieldToFilter('main_table.m_order_id', ['eq' => $mShipmentId]);
    }

    public function sortDesc()
    {
        return $this->shipmentCollection->setShipment('main_table.created_at', 'DESC');
    }

    public function filterByShipmentStatus($status)
    {
        return $this->shipmentCollection->addFieldToFilter('eos_order.status', ['in' => implode(",", $status)]);
    }

    public function filterByMagentoShipmentId($mShipmentId)
    {
        return $this->shipmentCollection->addFieldToFilter('main_table.m_order_id', ['eq' => $mShipmentId]);

    }

    public function getShipmentPrice($shipmentId, $total = false)
    {
        return $this->helperPrice->calculatePrice($shipmentId, $total);
    }

    public function getAwbStatus($shipmentId)
    {
        return $this->helperApiCallSF->getAwbStatus($shipmentId);
    }

    public function getEmptyShipmentsMessage()
    {
        return __('You have placed no Shipments yet.');
    }
    public function getEmptyCreateShipmentMessage()
    {
        return __('There still is no parcel information uploaded yet. Please do ');
    }
}