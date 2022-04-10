<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sunflowerbiz\Wechat\Model\Config\Source\Order\Status;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Config\Source\Order\Status;

/**
 * Order Status source model
 */
class Cancelpayment extends Status
{
    /**
     * @var string[]
     */
    protected $_stateStatuses =  \Magento\Sales\Model\Order::STATE_CANCELED;
}
