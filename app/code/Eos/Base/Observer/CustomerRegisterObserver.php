<?php
namespace Eos\Base\Observer;

use Magento\Framework\Event\ObserverInterface;
use Eos\Base\Helper\Email;


class CustomerRegisterObserver implements ObserverInterface
{
    private Email $helperEmail;

    public function __construct(
        Email $helperEmail
    ) {
        $this->helperEmail = $helperEmail;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
       // return $this->helperEmail->sendEmail();
    }
}
