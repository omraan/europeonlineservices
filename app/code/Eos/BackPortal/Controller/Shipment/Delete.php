<?php

namespace Eos\BackPortal\Controller\Shipment;

use Eos\Base\Model\ShipmentFactory;
use Eos\Base\Model\OrderFactory;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Delete extends \Magento\Framework\App\Action\Action
{

    /**
     * @var SessionFactory
     */
    protected $_customerSession;

    /**
     * @var DateTime
     */

    protected $_dateTime;

    /**
     * @var ShipmentFactory
     */

    protected $_shipment;

    /**
     * @var OrderFactory
     */

    protected $_order;

    public function __construct(
        Context $context,
        SessionFactory $customerSession,
        DateTime $dateTime,
        ShipmentFactory $shipment,
        OrderFactory $order
    ) {
        $this->_customerSession = $customerSession;
        $this->_dateTime = $dateTime;
        $this->_shipment = $shipment;
        $this->_order = $order;
        parent::__construct($context);
    }
    public function execute()
    {
        $post = $this->getRequest()->getParams();
        $orderModel = $this->_order->create()->load($post['shipment'], 'shipment_id')->setData('shipment_id', 0);
        $orderModel->save();


        // Create Parcels Record
        $shipmentModel = $this->_shipment->create();
        $shipmentModel->load($post['shipment']);
        $shipmentModel->delete();

        if ($shipmentModel->delete()) {
            $this->messageManager->addSuccessMessage(__('You succesfully deleted this record.'));
        } else {
            $this->messageManager->addErrorMessage(__('Delete went unsuccesful.'));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('orders/shipment/status');
        return $resultRedirect;
    }
}
