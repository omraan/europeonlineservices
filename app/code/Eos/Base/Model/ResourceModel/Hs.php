<?php
namespace Eos\Base\Model\ResourceModel;
class Hs extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('eos_hs', 'entity_id');
    }
}
