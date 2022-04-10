<?php

namespace Eos\WmsPortal\Controller\Parcels;

use Eos\Base\Model\ShipmentFactory;
use Eos\Base\Model\OrderFactory;
use Eos\Base\Model\ParcelFactory;
use Eos\Base\Model\ResourceModel\Parcel\Collection as ParcelCollection;
use Eos\Base\Model\ResourceModel\Parcel\CollectionFactory as ParcelCollectionFactory;
use Eos\Base\Model\ResourceModel\OrderDetails\CollectionFactory as OrderDetailsCollectionFactory;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use function Eos\WmsPortal\Controller\Warehouse\__;

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
     * @var ParcelFactory
     */

    protected $_parcel;

    /**
     * @var ParcelCollectionFactory
     */
    protected $_parcelCollection;

    /**
     * @var OrderDetailsCollectionFactory
     */
    protected $_orderDetailsCollection;

    /**
     * @var OrderFactory
     */

    protected $_order;

    /**
     * @var ShipmentFactory
     */

    protected $_shipment;

    public function __construct(
        Context $context,
        SessionFactory $customerSession,
        DateTime $dateTime,
        ParcelFactory $parcel,
        ParcelCollectionFactory $parcelCollection,
        OrderDetailsCollectionFactory $orderDetailsCollection,
        OrderFactory $order,
        ShipmentFactory $shipment
    ) {
        $this->_customerSession = $customerSession;
        $this->_dateTime = $dateTime;
        $this->_parcel = $parcel;
        $this->_parcelCollection = $parcelCollection;
        $this->_orderDetailsCollection = $orderDetailsCollection;
        $this->_order = $order;
        $this->_shipment = $shipment;
        parent::__construct($context);
    }
    public function execute()
    {


        $post = $this->getRequest()->getParams();

        /** @var $parcelCollection ParcelCollection */
        $parcelCollection = $this->_parcelCollection->create()->addFieldToFilter('tracking_number', $post['tracking_number']);

        if($parcelCollection->getSize() === 1) {
            $orderModel = $this->_order->create()->load($post['tracking_number'], 'webshop_tracking_number');

            $orderModel->setData('status', ($orderModel->getData('shipment_id') > 0 ? 'Shipment created' : 'open:pricing'));


            if ($orderModel->getData('shipment_id') > 0) {
                $orderModel->setData('status', 'Shipment created');

                $shipmentModel = $this->_shipment->create()->load($orderModel['shipment_id']);
                $shipmentModel->setData('status', 'open');
                $shipmentModel->save();
            } else {

                $orderDetailsCollection = $this->_parcelCollection->create()->addFieldToFilter('order_id', $orderModel['entity_id']);

                // Find out on which stage customer is with initial draft.
                if($orderModel->getData('webshop_order_total_price_net') > 0) {
                    $orderModel->setData('status', 'open:pricing');
                } elseif ( $orderDetailsCollection->getSize() > 0 ) {
                    $orderModel->setData('status', 'open:product');
                } elseif ( $orderModel->getData('warehouse_id') > 0) {
                    $orderModel->setData('status', 'open:warehouse');
                } else {
                    $orderModel->setData('status', 'open:webshop');
                }
            }
            $orderModel->save();
        }

        $parcelModel = $this->_parcel->create()->load($post['entity_id']);
        $parcelModel->delete();

        if ($parcelModel->delete()) {
            $this->messageManager->addSuccessMessage('You succesfully deleted this record.');
        } else {
            $this->messageManager->addErrorMessage('Delete went unsuccesful.');
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('wms/parcels');
        return $resultRedirect;
    }
}
