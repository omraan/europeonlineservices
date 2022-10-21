<?php

namespace Eos\Base\Api\Data;

interface OrderResultInterface
{
    /**
     * Return localized parameters for the application
     *
     * @return \Eos\Base\Api\Data\OrderDetailsItemInterface[]
     */
    public function getLocalization();
}
