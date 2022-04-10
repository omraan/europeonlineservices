<?php
namespace Eos\Base\Model\ResourceModel;
class Parcel extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('eos_parcel', 'entity_id');
    }
}
