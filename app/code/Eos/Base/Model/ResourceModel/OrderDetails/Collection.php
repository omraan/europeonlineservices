<?php
namespace Eos\Base\Model\ResourceModel\OrderDetails;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Eos\Base\Model\OrderDetails',
            'Eos\Base\Model\ResourceModel\OrderDetails'
        );
    }

    public function getOrderInfo()
    {
        $this->getSelect()->joinLeft(
            ['eos_order' => $this->getTable('eos_order')], //2nd table name by which you want to join
            'eos_order.entity_id = main_table.order_id', // common column which available in both table
            [
                '*',
                'order_entity_id' => 'eos_order.entity_id'
            ]
        );

        return $this;
    }
}
