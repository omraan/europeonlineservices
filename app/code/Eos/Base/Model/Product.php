<?php
namespace Eos\Base\Model;
use Magento\Framework\Model\AbstractModel;
class Product extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Eos\Base\Model\ResourceModel\Product');
    }
}
