<?php
namespace Eos\Base\Model\ResourceModel;
class Country extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('eos_country', 'entity_id');
    }
}
