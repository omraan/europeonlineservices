<?php

namespace Eos\WmsPortal\Controller\Book;
use Magento\Framework\App\Action\Context;
use Eos\Base\Model\OrderFactory;

class GetAwbStatus extends \Magento\Framework\App\Action\Action
{
    /**
     * @var OrderFactory
     */

    protected $_order;

    public function __construct(
        Context $context,
        OrderFactory $order

    ) {
        parent::__construct($context);
        $this->_order = $order;
    }
    public function execute()
    {
        $post = $this->getRequest()->getParams();
        $post['awb_code'];



        try {
            $orderModel = $this->_order->create()->load($post['outbound_tracking_number'] , 'awb_code');

            echo '<pre>';
            var_dump($orderModel['awb_code'] );
            die();
            echo json_encode($resultArray);
        } catch (Exception $e) {
            exit($e);
        }

    }
}
