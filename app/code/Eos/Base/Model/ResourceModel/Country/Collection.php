<?php
namespace Eos\Base\Model\ResourceModel\Country;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Eos\Base\Model\Country',
            'Eos\Base\Model\ResourceModel\Country'
        );
    }
}
