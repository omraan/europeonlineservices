<?php
namespace Eos\Base\Model\ResourceModel;
class Shipment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('eos_shipment', 'entity_id');
    }
}
