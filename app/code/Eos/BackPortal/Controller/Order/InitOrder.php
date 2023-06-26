<?php

namespace Eos\BackPortal\Controller\Order;

use Eos\Base\Model\OrderFactory;
use Eos\Base\Model\ResourceModel\Order\Collection as OrderCollection;
use Eos\Base\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;


class InitOrder extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var OrderFactory
     */

    protected $_order;

    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    public function __construct(
        Context $context,
        Session $customerSession,
        OrderFactory $order,
        OrderCollectionFactory $orderCollectionFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_order = $order;
        $this->_orderCollectionFactory = $orderCollectionFactory;

        parent::__construct($context);
    }
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        // Check if user is logged-in
        if ($this->_customerSession->isLoggedIn()) {
            $post = $this->getRequest()->getPostValue();

            if ($post['continue'] === 'yes') {
                $statusArray = ['open:init', 'open:webshop', 'open:warehouse', 'open:product'];

                /** @var $orderCollection OrderCollection */
                $orderCollection = $this->_orderCollectionFactory->create();
                $orderCollection->addFieldToFilter('customer_id', ['eq' => $this->_customerSession->getCustomer()->getId()]);
                $orderCollection->addFieldToFilter('status', ['in' => $statusArray]);

                $orderId = $orderCollection->getFirstItem()->getId();
                $resultRedirect->setPath('portal/order/create', ['order' => $orderId]);
                return $resultRedirect;
            } else {
                $statusArray = ['open:init', 'open:webshop', 'open:warehouse', 'open:product'];
                $this->_order->create()->getResource()->deleteOrdersByStatus($statusArray);

                // Create Order Record
                $orderModel = $this->_order->create();
                $orderModel->setData('customer_id', $this->_customerSession->getCustomer()->getId());
                $orderModel->setData('status', 'open:init');
                $orderModel->save();

                $orderId = $orderModel->getId();
                $resultRedirect->setPath('portal/order/create', ['order' => $orderId, 'new' => '1']);
                return $resultRedirect;
            }
        } else {
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }
    }


}
