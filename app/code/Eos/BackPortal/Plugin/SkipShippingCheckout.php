<?php

namespace Eos\BackPortal\Plugin;

use Magento\Quote\Model\Quote;

class SkipShippingCheckout
{

    public function afterIsVirtual()
    {
        return false;
    }
}
