<?php

namespace Eos\BackPortal\Controller\Order;

use Eos\Base\Model\OrderFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;


class AppendOrderWarehouse extends \Magento\Framework\App\Action\Action
{

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var OrderFactory
     */

    protected $_order;

    public function __construct(
        Context $context,
        Session $customerSession,
        OrderFactory $order
    ) {
        $this->_customerSession = $customerSession;
        $this->_order = $order;
        parent::__construct($context);
    }
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        // Check if user is logged-in
        if ($this->_customerSession->getCustomer()->getId() > 0) {
            $post = $this->getRequest()->getParams();
            // Create Order Record
            $orderModel = $this->_order->create()->load(intval($post['general'][0]['order_id']), 'entity_id');
            $orderModel->setData('warehouse_id', intval($post['warehouse'][0]['warehouse_id']));
            $orderModel->setData('status', 'open:warehouse');
            $orderModel->save();

            var_dump('done');


        } else {
            $resultRedirect->setPath('customer/account/login');
        }
    }
}
