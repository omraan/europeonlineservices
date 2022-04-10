<?php
namespace Eos\Base\Model\ResourceModel;
class BatchPallet extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('eos_batch_pallet', 'entity_id');
    }
}
