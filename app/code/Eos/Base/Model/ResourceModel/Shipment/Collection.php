<?php
namespace Eos\Base\Model\ResourceModel\Shipment;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Eos\Base\Model\Shipment',
            'Eos\Base\Model\ResourceModel\Shipment'
        );
    }

    public function getShipmentOrders()
    {
        $this->getSelect()->joinLeft(
            ['eos_order' => $this->getTable('eos_order')], //2nd table name by which you want to join
            'eos_order.shipment_id = main_table.entity_id', // common column which available in both table
            [
                '*',
                'order_id' => 'eos_order.entity_id',
                'order_status' => 'eos_order.status',
                'shipment_status' => 'main_table.status'
            ]
        );

        return $this;
    }

        public function getShipmentOrdersDetails()
    {
        $select = $this->getShipmentOrders();

        $select->getSelect()
            ->joinLeft(
            ['eos_order_details' => $this->getTable('eos_order_details')],
            'eos_order_details.order_id = eos_order.entity_id',
                [
                    '*',
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

        return $select;
    }
}
