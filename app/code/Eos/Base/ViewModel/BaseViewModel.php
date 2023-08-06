<?php

namespace Eos\Base\ViewModel;

use Magento\Customer\Model\SessionFactory;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class BaseViewModel implements ArgumentInterface
{
    /**
     * @var SessionFactory
     */
    protected $customerSession;

    public function __construct(
        SessionFactory $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    public function getCustomer()
    {
        return $this->customerSession->create()->getCustomer();
    }


    // Other common methods can also be defined here
}