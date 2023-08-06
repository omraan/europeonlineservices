<?php

namespace Eos\Base\Model\ResourceModel\Product;

use Eos\Base\Model\ResourceModel\Product;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\Eos\Base\Model\Product::class, Product::class);
    }

    /**
     * Retrieve distinct values of a specific column
     *
     * @param string $column
     * @return array
     */
    public function getDistinctValues($column)
    {
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $this->getSelect()->distinct(true)->columns($column);
        return $this->getConnection()->fetchCol($this->getSelect());
    }
}
