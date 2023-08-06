<?php
namespace Eos\Base\Model\ResourceModel;
class SfToken extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('eos_sf_token', 'entity_id');
    }
}
