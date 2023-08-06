<?php
namespace Eos\Base\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Environment implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'test', 'label' => __('Test')],
            ['value' => 'production', 'label' => __('Production')],
        ];
    }
}
