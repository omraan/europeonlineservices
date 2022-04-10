<?php

namespace Eos\BackPortal\Controller\Order;

use Eos\Base\Model\OrderFactory;
use Eos\Base\Model\ShipmentFactory;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;

class GetInvoice extends \Magento\Framework\App\Action\Action
{
    /**
     * @var ShipmentFactory
     */

    protected $_shipment;

    /**
     * @var OrderFactory
     */

    protected $_order;

    /**
     * @var StoreManagerInterface
     */

    protected $_storeManager;

    public function __construct(
        Context $context,
        ShipmentFactory $shipment,
        OrderFactory $order,
        StoreManagerInterface $storeManager
    ) {
        $this->_shipment = $shipment;
        $this->_order = $order;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }
    public function execute()
    {

        $post = $this->getRequest()->getParams();

        $shipment = $this->_shipment->create()->load($post['awb'], 'awb_code')['entity_id'];

        $pdf = $this->_order->create()->load($shipment, 'shipment_id')['webshop_tracking_number'] . '.pdf';

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($this->_storeManager->getStore()->getBaseUrl() . 'media/' . $pdf);
        return $resultRedirect;
    }
}
