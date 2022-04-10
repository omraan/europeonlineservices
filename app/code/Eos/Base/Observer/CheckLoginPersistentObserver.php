<?php

namespace Eos\Base\Observer;

use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

class CheckLoginPersistentObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */

    protected $_customerSession;

    /**
     * @var StoreManagerInterface
     */

    protected $_storeManager;

    /**
     * @var UrlInterface
     */

    protected $_url;

    /**
     * @var ResponseFactory
     */
    protected $_responseFactory;


    protected $_request;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        ResponseFactory $responseFactory,
        UrlInterface $url,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->_customerSession = $customerSession;
        $this->redirect = $redirect;
        $this->_responseFactory = $responseFactory;
        $this->_url = $url;
        $this->_request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $actionName = $observer->getEvent()->getRequest()->getFullActionName();
        $controller = $observer->getControllerAction();

        $controllerNew = $this->_request->getControllerName();

        $openActions = [
            'create',
            'createpost',
            'login',
            'loginpost',
            'logoutsuccess',
            'forgotpassword',
            'forgotpasswordpost',
            'resetpassword',
            'resetpasswordpost',
            'confirm',
            'confirmation',
            'index'
        ];

        $warehouseActions = [
            'addbatch',
            'batches',
            'index',
            'editbatch'
        ];

        if ($this->_request->getControllerName() == 'account' ||
            $this->_request->getModuleName() == 'frontportal'||
            $this->_request->getModuleName() == 'wms') {
            return $this; //if in allowed actions do nothing.
        }

        if ($this->_customerSession->isLoggedIn()) {
            if ($this->_request->getControllerName() == 'warehouse' && $this->_customerSession->getCustomer()->getGroupId() != '4') {
                $this->setRedirectLogin();
            }
        }/*else {
            $this->setRedirectLogin();
        }*/
        return $this;


    }

    public function setRedirectLogin()
    {
        $CustomRedirectionUrl = $this->_url->getUrl('customer/account/login');
        $this->_responseFactory->create()->setRedirect($CustomRedirectionUrl)->sendResponse();
        /* die use for stop excaution */
        die();
    }
}
