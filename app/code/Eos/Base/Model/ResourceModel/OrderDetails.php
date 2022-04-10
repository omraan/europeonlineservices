<?php
namespace Eos\Base\Model\ResourceModel;
class OrderDetails extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('eos_order_details', 'entity_id');
    }
}
