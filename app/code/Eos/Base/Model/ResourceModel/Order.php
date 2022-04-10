<?php
namespace Eos\Base\Model\ResourceModel;
class Order extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    const MAIN_TABLE = 'eos_order';
    const ID_FIELD_NAME = 'entity_id';

    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, self::ID_FIELD_NAME);
    }
}
