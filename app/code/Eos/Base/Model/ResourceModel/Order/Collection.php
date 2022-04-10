<?php
namespace Eos\Base\Model\ResourceModel\Order;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_mainTable;

    /**
     * Define model & resource model
     */

    protected function _construct()
    {
        $this->_init(
            'Eos\Base\Model\Order',
            'Eos\Base\Model\ResourceModel\Order'
        );
    }

    public function getShipmentInfo() {
        $this->getSelect()->joinLeft(
            ['eos_shipment' => $this->getTable('eos_shipment')], //2nd table name by which you want to join
            'eos_shipment.entity_id = main_table.shipment_id', // common column which available in both table
            [
                'shipment_status' => 'eos_shipment.status',
                'awb_code',
                'printUrl',
                'invoiceUrl',
                'total_weight'
            ]
        );
        return $this;
    }

    public function getUnregisteredParcelInfo()
    {
        $this->getSelect()->joinLeft(
            ['eos_parcel' => $this->getTable('eos_parcel')], //2nd table name by which you want to join
            'eos_parcel.tracking_number = main_table.webshop_tracking_number', // common column which available in both table
            [
                'total_parcels' => 'count(eos_parcel.tracking_number)'
            ]
        )->group(
            ['main_table.webshop_tracking_number']
        )->having(
            'count(eos_parcel.tracking_number) = 0'
        );

        return $this;
    }

    public function getRegisteredParcelInfo()
    {
        $this->getSelect()->joinLeft(
            ['eos_parcel' => $this->getTable('eos_parcel')], //2nd table name by which you want to join
            'eos_parcel.tracking_number = main_table.webshop_tracking_number', // common column which available in both table
            [
                'total_parcels' => 'count(eos_parcel.tracking_number)'
            ]
        )->joinLeft(
            ['eos_shipment' => $this->getTable('eos_shipment')], //2nd table name by which you want to join
            'eos_shipment.entity_id = main_table.shipment_id', // common column which available in both table
            [
                'shipment_status' => 'eos_shipment.status',
                'awb_code'
            ]
        )->group(
            ['main_table.webshop_tracking_number']
        )->having(
            'count(eos_parcel.tracking_number) > 0'
        );

        return $this;
    }

    /*
        /**
         * Set customer filter
         *
         * @param \Magento\Customer\Model\Customer|array $customer
         * @return $this

    public function setCustomerFilter($customer)
    {
        if (is_array($customer)) {
            $this->addFieldToFilter('customer_id', ['in' => $customer]);
        } elseif ($customer->getId()) {
            $this->addFieldToFilter('customer_id', $customer->getId());
        } else {
            $this->addFieldToFilter('customer_id', '-1');
        }
        return $this;
    }*/

}
