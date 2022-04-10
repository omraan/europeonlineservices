<?php

/**
 *
 */
namespace Eos\Base\Plugin;

use Magento\Customer\Model\Session;

/**
 *
 */
class LoginPostPlugin
{
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * LoginPostPlugin constructor.
     * @param Session $_customerSession
     */
    public function __construct(
        Session $_customerSession
    ) {
        $this->_customerSession = $_customerSession;
    }

    /**
     * Change redirect after login to home instead of dashboard.
     *
     * @param \Magento\Customer\Controller\Account\LoginPost $subject
     * @param \Magento\Framework\Controller\Result\Redirect $result
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function afterExecute(
        \Magento\Customer\Controller\Account\LoginPost $subject, $result
    ) {
        // Check if Customer Group is Warehouse
        if ($this->_customerSession->getCustomerGroupId() == 4) {
            $result->setPath('wms');
        } else {
            $result->setPath('customer/account');
        }

        return $result;
    }
}
