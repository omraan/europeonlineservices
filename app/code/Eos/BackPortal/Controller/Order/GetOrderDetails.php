<?php

namespace Eos\BackPortal\Controller\Order;

use Eos\Base\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;

class GetOrderDetails extends \Magento\Framework\App\Action\Action
{

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;


    public function __construct(
        Context $context,
        Session $customerSession,
        OrderCollectionFactory $orderCollectionFactory,
    ) {
        $this->_customerSession = $customerSession;
        $this->orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context);
    }
    public function execute()
    {
        if ($this->_customerSession->getCustomer()->getId() > 0) {
            $post = $this->getRequest()->getParams();
            $orderDetails = $this->orderCollectionFactory->create();
            $orderDetails->getOrdersDetails();
            $orderDetails->addFieldToFilter('order_id', ['eq' => $post['order_id']]);
            
            $array = [];
            foreach ($orderDetails->getItems() as $item) {
                $array[] = $item->toArray();
            }
            echo json_encode($array);

        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/login');
        }
    }
}
