<?php

namespace Eos\BackPortal\Controller\Shipment;

use Magento\Customer\Model\SessionFactory as Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey;
use Eos\Base\Helper\ApiCallSF;

class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var FormKey
     */

    protected $_formKey;

    /**
    * @var ApiCallSF
    */
    protected $_helperApiCallSF;

    public function __construct(
        Context $context,
        Session $customerSession,
        FormKey $formKey,
        ApiCallSF $helperApiCallSF
    ) {
        $this->_customerSession = $customerSession;
        $this->_formKey = $formKey;
        $this->_helperApiCallSF = $helperApiCallSF;
        parent::__construct($context);
    }
    public function execute()
    {
        $customer = $this->_customerSession->create();
        $customerId =  $customer->getCustomer()->getId();
        $resultRedirect = $this->resultRedirectFactory->create();

        // Check if user is logged-in
        if ($customerId > 0) {
            // Params are order_{orderID} + Form_key
            $post = $this->getRequest()->getParams();

            $orders = [];
            // Loop through all order_{orderID}
            $i = 0;
            $string = "order";
            foreach ($post as $key => $value) {
                if (str_starts_with($key, $string)) {
                    $orders[$i]['entity_id'] = explode("_", $key)[1];
                }
                $i++;
            }

            $shipmentId = $this->_helperApiCallSF->ReConfrimWeightOrder($customerId, null, $orders)['shipment_id'];

            $resultRedirect->setPath('portal/uploadid/create', ['shipment' => $shipmentId]);
        } else {
            $resultRedirect->setPath('customer/account/login');
        }
        return $resultRedirect;
    }
}
