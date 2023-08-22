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
                    'order_details_id' => 'eos_order_details.entity_id',
                    'product_name' => 'eos_order_details.product_title'
                ]

            )->joinLeft(
                ['eos_product' => $this->getTable('eos_product')],
                'eos_product.id = eos_order_details.product_id',
                ['*']

            )
            ;

        return $select;
    }
}
