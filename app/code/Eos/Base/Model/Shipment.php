<?php
namespace Eos\Base\Model;

use Magento\Framework\Model\AbstractModel;
use Eos\Base\Model\ResourceModel\Shipment\CollectionFactory as ShipmentCollectionFactory;

class Shipment extends AbstractModel
{

    protected function _construct()
    {
        $this->_init(ResourceModel\Shipment::class);
    }

    /**
     * @var ShipmentCollectionFactory
     */
    protected $shipmentCollectionFactory;

    public function __construct(
        ShipmentCollectionFactory $shipmentCollectionFactory,
        // ... other dependencies ...
    ) {
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        // ... initialize other dependencies ...

        parent::__construct();
    }

    public function getId()
    {
        return $this->getData('entity_id');
    }

    public function getCustomerId()
    {
        return $this->getData('customer_id');
    }

    public function setCustomerId($customerId)
    {
        return $this->setData('customer_id', $customerId);
    }

    public function getFShipmentId()
    {
        return $this->getData('f_shipment_id');
    }

    public function setFShipmentId($fShipmentId)
    {
        return $this->setData('f_shipment_id', $fShipmentId);
    }

    public function getAwbCode()
    {
        return $this->getData('awb_code');
    }

    public function setAwbCode($awbCode)
    {
        return $this->setData('awb_code', $awbCode);
    }

    public function getTotalWeight()
    {
        return $this->getData('total_weight');
    }

    public function setTotalWeight($totalWeight)
    {
        return $this->setData('total_weight', $totalWeight);
    }

    public function getOriginCode()
    {
        return $this->getData('originCode');
    }

    public function setOriginCode($originCode)
    {
        return $this->setData('originCode', $originCode);
    }

    public function getDestCode()
    {
        return $this->getData('destCode');
    }

    public function setDestCode($destCode)
    {
        return $this->setData('destCode', $destCode);
    }

    public function getPrintCode()
    {
        return $this->getData('printCode');
    }

    public function setPrintCode($printCode)
    {
        return $this->setData('printCode', $printCode);
    }

    public function getPrintUrl()
    {
        return $this->getData('printUrl');
    }

    public function setPrintUrl($printUrl)
    {
        return $this->setData('printUrl', $printUrl);
    }

    public function getInvoiceUrl()
    {
        return $this->getData('invoiceUrl');
    }

    public function setInvoiceUrl($invoiceUrl)
    {
        return $this->setData('invoiceUrl', $invoiceUrl);
    }

    /**
     * Get shipments
     *
     * @return \Eos\Base\Model\ResourceModel\Shipment\Collection
     */

}