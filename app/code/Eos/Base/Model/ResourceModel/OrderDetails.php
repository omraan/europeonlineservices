<?php declare(strict_types=1);

namespace Eos\Base\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class OrderDetails extends AbstractDb
{
    const MAIN_TABLE = 'eos_order_details';
    const ID_FIELD_NAME = 'entity_id';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, self::ID_FIELD_NAME);
    }
}
