<?php

namespace app\code\Sunflowerbiz\Wechat\Model\Config\Source\Order\Status\Processing;
use \Magento\Sales\Model\Config\Source\Order\Status\Processing as OProcess;

class Processing  extends OProcess
{

    public function toOptionArray()
    {
        $statuses = $this->_stateStatuses
            ? $this->_orderConfig->getStateStatuses($this->_stateStatuses)
            : $this->_orderConfig->getStatuses();

        foreach ($statuses as $code => $label) {
            $options[] = ['value' => $code, 'label' => $label];
        }
        return $options;
    }
}