<?php

namespace Eos\Base\Model\ResourceModel\Parcel;

use Eos\Base\Model\ResourceModel\Parcel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\Eos\Base\Model\Parcel::class, Parcel::class );
    }
}
