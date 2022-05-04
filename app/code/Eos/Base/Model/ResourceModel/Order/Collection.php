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

    public function getOrdersDetails()
    {
        $this->getSelect()
            ->joinLeft(
                ['eos_order_details' => $this->getTable('eos_order_details')],
                'eos_order_details.order_id = main_table.entity_id',
                [
                    '*',
                    //'order_id' => 'main_table.entity_id',
                    'orderdetails_id' => 'eos_order_details.order_id',
                    'product_name' => 'eos_order_details.product_title'
                ]

            )->joinLeft(
                ['eos_hs' => $this->getTable('eos_hs')],
                'eos_hs.product_tax_nr = eos_order_details.product_tax_nr',
                ['*']

            )->joinLeft(
                ['eos_hs_category' => $this->getTable('eos_hs_category')],
                'eos_hs_category.category_id = eos_hs.category_id',
                ['category_title','category_lang'=>'lang']

            )->joinLeft(
                ['eos_hs_subcategory' => $this->getTable('eos_hs_subcategory')],
                'eos_hs_subcategory.subcategory_id = eos_hs.subcategory_id and eos_hs_subcategory.category_id = eos_hs.category_id',
                ['subcategory_title','subcategory_lang'=>'lang']

            )->joinLeft(
                ['eos_hs_product' => $this->getTable('eos_hs_product')],
                'eos_hs_product.product_tax_nr = eos_hs.product_tax_nr',
                ['product_title','product_lang'=>'lang']

            )->joinLeft(
                ['eos_hs_china' => $this->getTable('eos_hs_china')],
                'eos_hs_china.product_tax_nr = eos_hs.product_tax_nr',
                ['volume_min','volume_max','tax_rate','calculate_ind']

            )
        ;

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
