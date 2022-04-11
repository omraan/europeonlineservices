<?php

namespace Eos\BackPortal\Observer;

use Eos\Base\Model\OrderFactory;
use Eos\Base\Model\ShipmentFactory;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\AddressFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\SessionFactory;
use Eos\Base\Helper\ApiCallSF;

class ConfirmPayment implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var ShipmentFactory
     */
    protected $_shipment;

    /**
     * @var SessionFactory\
     */
    protected $_checkoutSessionFactory;

    /** @var ApiCallSF */

    protected $_helperApiCallSF;

    public function __construct(
        Session $_customerSession,
        ShipmentFactory $shipment,
        SessionFactory $checkoutSessionFactory,
        ApiCallSF $helperApiCallSF
    ) {
        $this->_customerSession = $_customerSession;
        $this->_shipment = $shipment;
        $this->_checkoutSessionFactory = $checkoutSessionFactory;
        $this->_helperApiCallSF = $helperApiCallSF;
    }

    public function execute(Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $shipmentCollection = $objectManager->get('\Eos\Base\Model\Shipment')->getCollection();
        $shipmentCollection->addFieldToFilter('status', ['eq'=>'During payment'])->addFieldToFilter('customer_id', ['eq'=>$this->_customerSession->getCustomer()->getId()]);
        $shipment = $shipmentCollection->getFirstItem();

        /** @var CheckoutSession $orderObj */
        $orderObj = $this->checkoutSessionFactory->create()->getLastRealOrder();
        $customerId = $this->_customerSession->getCustomer()->getId();

        $apiCall = $this->_helperApiCallSF->ReConfrimWeightOrder($customerId, $shipment['entity_id'], null)['success'];

        $shipmentModel = $this->_shipment->create()->load($shipment['entity_id']);
        $shipmentModel->setData('m_order_id', $orderObj->getEntityId());
        $shipmentModel->setData('status', $apiCall ? 'Payed' : 'Payed but error');
        $shipmentModel->save();


    }
}
