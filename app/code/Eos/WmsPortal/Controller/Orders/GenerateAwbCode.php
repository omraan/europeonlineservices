<?php

namespace Eos\WmsPortal\Controller\Orders;

use Eos\Base\Helper\ApiCallSF;
use Magento\Framework\App\Action\Context;

class GenerateAwbCode extends \Magento\Framework\App\Action\Action
{


    /** @var ApiCallSF */

    protected $helperApiCallSF;

    // Instantiating the Context object is no longer required
    public function __construct(
        Context $context,
        ApiCallSF $helperApiCallSF
    ) {
        // Calling parent::__construct() is also no longer needed
        $this->helperApiCallSF = $helperApiCallSF;
        parent::__construct($context);
    }

    public function execute()
    {

        $post = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $this->helperApiCallSF->CancelOrderService($post['shipment']);
            $this->helperApiCallSF->ReConfrimWeightOrder(false,$post['shipment'],null,true);
            $this->messageManager->addSuccessMessage(__('You generated a new awb code.'));
            $resultRedirect->setPath('wms/orders/order' , ['order_id' => $post['order_id'] ]);
            return $resultRedirect;
        } catch (\Exception $e) {


            echo "doest not work";
        }
    }
}
