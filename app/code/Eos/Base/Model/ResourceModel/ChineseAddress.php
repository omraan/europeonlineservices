<?php
namespace Eos\Base\Model\ResourceModel;
class ChineseAddress extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('address_cn_data', 'id');
    }
}
