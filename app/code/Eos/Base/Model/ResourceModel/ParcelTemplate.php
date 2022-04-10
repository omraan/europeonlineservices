<?php
namespace Eos\Base\Model\ResourceModel;
class ParcelTemplate extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('eos_parcel_template', 'entity_id');
    }
}
