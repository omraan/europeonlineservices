<?php

namespace Eos\WmsPortal\Controller\Orders;

use Eos\Base\Helper\ApiCallSF;
use Eos\Base\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Framework\App\Action\Context;

class SeperateOrder extends \Magento\Framework\App\Action\Action
{


    /** @var ApiCallSF */

    protected $helperApiCallSF;

    /**
     * @var OrderCollectionFactory
     */

    protected $_orderCollectionFactory;

    // Instantiating the Context object is no longer required
    public function __construct(
        Context $context,
        ApiCallSF $helperApiCallSF,
        OrderCollectionFactory $orderCollectionFactory
    ) {
        // Calling parent::__construct() is also no longer needed
        $this->helperApiCallSF = $helperApiCallSF;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {

        $post = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        $order[0]['entity_id'] = $post['order_id'];

        $customerId = $this->_orderCollectionFactory->create()->addFieldToFilter('main_table.entity_id', $post['order_id'])->getFirstItem()['customer_id'];


        try {
            $this->helperApiCallSF->ReConfrimWeightOrder($customerId,null, $order,false);
            $this->messageManager->addSuccessMessage(__('You separated this order.'));
            $resultRedirect->setPath('wms/orders/order' , ['order_id' => $post['order_id'] ]);
            return $resultRedirect;

        } catch (\Exception $e) {


            echo "doest not work";
        }
    }
}
