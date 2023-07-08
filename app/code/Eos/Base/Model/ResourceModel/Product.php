<?php
namespace Eos\Base\Model\ResourceModel;
class Product extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('eos_product', 'entity_id');
    }
}
