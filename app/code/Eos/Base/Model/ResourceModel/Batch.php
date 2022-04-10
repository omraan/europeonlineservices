<?php
namespace Eos\Base\Model\ResourceModel;
class Batch extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('eos_batch', 'entity_id');
    }
}
