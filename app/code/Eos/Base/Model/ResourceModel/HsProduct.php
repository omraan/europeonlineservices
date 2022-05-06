<?php
namespace Eos\Base\Model\ResourceModel;
class HsProduct extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('eos_hs_product', 'entity_id');
    }
}
