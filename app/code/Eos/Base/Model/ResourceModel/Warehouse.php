<?php
namespace Eos\Base\Model\ResourceModel;
class Warehouse extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('eos_warehouse', 'entity_id');
    }
}
