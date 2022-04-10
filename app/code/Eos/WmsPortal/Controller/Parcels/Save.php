<?php

namespace Eos\WmsPortal\Controller\Parcels;

use Eos\Base\Model\OrderFactory;
use Eos\Base\Model\ParcelFactory;
use Eos\Base\Model\ResourceModel\Order\Collection as OrderCollection;
use Eos\Base\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Eos\Base\Model\ResourceModel\Parcel\Collection as ParcelCollection;
use Eos\Base\Model\ResourceModel\Parcel\CollectionFactory as ParcelCollectionFactory;
use Eos\Base\Model\ShipmentFactory;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Save extends \Magento\Framework\App\Action\Action
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

    /**
     * @var OrderCollectionFactory
     */

    protected $_orderCollectionFactory;

    /**
     * @var ParcelFactory
     */

    protected $_parcel;

    /**
     * @var ParcelCollectionFactory
     */

    protected $_parcelCollectionFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    public function __construct(
        Context $context,
        SessionFactory $customerSession,
        DateTime $dateTime,
        ShipmentFactory $shipment,
        OrderFactory $order,
        OrderCollectionFactory $orderCollectionFactory,
        ParcelFactory $parcel,
        ParcelCollectionFactory $parcelCollectionFactory,
        ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->_dateTime = $dateTime;
        $this->_shipment = $shipment;
        $this->_order = $order;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_parcelCollectionFactory = $parcelCollectionFactory;
        $this->_parcel = $parcel;
        $this->messageManager = $messageManager;
    }
    public function execute()
    {
        $post = $this->getRequest()->getParams();

        // Loop through all width_{counter}
        $string = "width";
        $countParcels=0;
        foreach ($post as $key => $value) {
            if (substr($key, 0, strlen($string)) == $string) {
                $countParcels++;
            }
        }

        $matchTrackingNumber = $this->_order->create()->load($post['tracking_number'], 'webshop_tracking_number')['webshop_tracking_number'];

        if (!$matchTrackingNumber) {
            $this->messageManager->addErrorMessage(__('Unknown Tracking Number: ' . $post['tracking_number']));
        } else {
            for ($i=1;$i<$countParcels;$i++) {
                // Create Parcels Record
                $parcelModel = $this->_parcel->create();
                $parcelModel->setData('tracking_number', $post['tracking_number']);
                $parcelModel->setData('created_at', $this->_dateTime->gmtDate());
                $parcelModel->setData('width', $post['width_' . $i]);
                $parcelModel->setData('height', $post['height_' . $i]);
                $parcelModel->setData('length', $post['length_' . $i]);
                $parcelModel->setData('weight', $post['weight_' . $i]);
                $parcelModel->save();
            }

            $shipment_id = $this->_orderCollectionFactory->create()->addFieldToFilter('webshop_tracking_number', $post['tracking_number'])->getFirstItem()['shipment_id'];

            $orderItems = $this->_orderCollectionFactory->create()->addFieldToFilter('shipment_id', $shipment_id)->getItems();

            $checkOrder = true;
            foreach ($orderItems as $item) {
                $parcelCollection = $this->_parcelCollectionFactory->create()->addFieldToFilter('tracking_number', $item['webshop_tracking_number']);
                if ($parcelCollection->getSize() === 0) {
                    $checkOrder = false;
                }
            }

            if ($checkOrder) {
                $shipmentModel = $this->_shipment->create()->load($shipment_id, 'entity_id');
                $shipmentModel->setData('status', 'Parcels Measured');
                $shipmentModel->save();
            }

            if ($parcelModel->save()) {
                $this->messageManager->addSuccessMessage(__('You saved the data.'));
            } else {
                $this->messageManager->addErrorMessage(__('Data was not saved.'));
            }
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($post['redirect']);
        return $resultRedirect;
    }
}
