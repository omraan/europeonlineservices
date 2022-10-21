<?php

namespace Eos\Base\Api;

use Eos\Base\Api\Data\OrderResultInterface;
use Magento\Framework\Exception\LocalizedException;

interface OrderOperatorInterface
{

    /**
     * @param int $id
     * @return OrderResultInterface
     * @throws LocalizedException
     */
    public function parse(int $id): OrderResultInterface;
}
