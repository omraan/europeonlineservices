<?php
namespace Eos\Base\Model\ResourceModel\Warehouse;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Eos\Base\Model\Warehouse',
            'Eos\Base\Model\ResourceModel\Warehouse'
        );
    }
}
