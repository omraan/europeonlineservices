<?php

namespace Eos\Base\Model\ResourceModel\Product;

use Eos\Base\Model\ResourceModel\Product;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\Eos\Base\Model\Product::class, Product::class );
    }
}
