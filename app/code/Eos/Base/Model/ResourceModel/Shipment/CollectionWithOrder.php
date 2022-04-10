<?php
namespace Eos\Base\Model\ResourceModel\Shipment;

class CollectionWithOrder extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
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

    protected function _initSelect()
    {
        parent::_initSelect();

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

        /*
                $this->getSelect()->joinLeft(
                    ['eos_order' => $this->getTable('eos_order')], //2nd table name by which you want to join
                    'eos_order.shipment_id = main_table.entity_id', // common column which available in both table
                   [
                       'main_table.customer_id',
                       'shipment_status' => 'main_table.status' ,
                       'shipment_created_at' => 'main_table.created_at',
                       'shipment_modified_at' => 'main_table.modified_at',
                       'order_id' => 'eos_order.entity_id',
                       'eos_order.shipment_id',
                       'eos_order.webshop_title',
                       'eos_order.webshop_order_nr',
                       'eos_order.webshop_tracking_number',
                       'order_status' => 'eos_order.status',
                       'order_created_at' => 'eos_order.created_at',
                       'order_modified_at' => 'eos_order.modified_at'
                   ] // '*' define that you want all column of 2nd table. if you want some particular column then you can define as ['column1','column2']
                );*/
    }
}
