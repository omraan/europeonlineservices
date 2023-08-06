<?php

namespace Eos\Base\ViewModel;

use Eos\Base\Helper\CalculatePrice as HelperPrice;
use Eos\Base\Model\ResourceModel\Shipment\CollectionFactory as ShipmentCollectionFactory;

class ShipmentViewModel extends BaseViewModel
{
    use CollectionMethodsTrait;
    private $shipmentCollectionFactory;
    private $helperPrice;

    protected $shipmentCollection;

    public function __construct(
        ShipmentCollectionFactory $shipmentCollectionFactory,
        HelperPrice $helperPrice
    ) {
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->helperPrice = $helperPrice;
    }
    
    public function getShipments()
    {
        if (!$this->shipmentCollection) {
            $this->shipmentCollection = $this->shipmentCollectionFactory->create();
        }
        $this->setCollection($this->shipmentCollection);
        return $this;
    }

    public function getShipmentOrders() {
        if (!$this->shipmentCollection) {
            $this->shipmentCollection = $this->shipmentCollectionFactory->create()->getShipmentOrders();
        }
        return $this;

    }

    public function filterByOrderStatus($status) {
        return $this->shipmentCollection->addFieldToFilter('eos_order.status', ['in' => implode(",", $status)]);
    }

    public function filterByMagentoOrderId($m_order_id) {
        return $this->shipmentCollection->addFieldToFilter('main_table.m_order_id', ['eq' => $m_order_id]);

    }

    public function getShipmentPrice($shipment_id, $total = false) {
        return $this->helperPrice->calculatePrice($shipment_id, $total);
    }

    
    
}