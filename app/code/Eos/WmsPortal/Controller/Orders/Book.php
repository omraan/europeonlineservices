<?php

namespace Eos\WmsPortal\Controller\Orders;

use Eos\Base\Model\OrderFactory;
use Eos\Base\Model\ParcelFactory;
use Eos\Base\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use function Eos\WmsPortal\Controller\Warehouse\__;

use Eos\Base\Helper\Email;

class Book extends \Magento\Framework\App\Action\Action
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
     * @var OrderFactory
     */

    protected $_order;

    /**
     * @var OrderCollectionFactory
     */

    protected $_orderCollection;

    /**
     * @var ParcelFactory
     */

    protected $_parcel;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /** @var Email */

    protected $helperEmail;


    public function __construct(
        Context $context,
        SessionFactory $customerSession,
        DateTime $dateTime,
        OrderCollectionFactory $orderCollection,
        OrderFactory $order,
        ParcelFactory $parcel,
        ManagerInterface $messageManager,
        Email $helperEmail
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->_dateTime = $dateTime;
        $this->_orderCollection = $orderCollection;
        $this->_order = $order;
        $this->_parcel = $parcel;
        $this->messageManager = $messageManager;
        $this->helperEmail = $helperEmail;
    }
    public function execute()
    {
        $post = $this->getRequest()->getParams();


        $orderModel = $this->_order->create()->load($post['webshop_tracking_number'], 'webshop_tracking_number');
        $shipmentId = $orderModel['shipment_id'];

        if ($post['type'] == 'unbook') {
            $orderModel->setData('book_ind', 0);
            $this->messageManager->addSuccessMessage('Parcel : ' . $post['webshop_tracking_number'] . ' has succesfully been unbooked.');
            $orderModel->save();
        }
        if ($post['type'] == 'onhold') {
            $orderModel->setData('book_ind', 9);
            $this->messageManager->addSuccessMessage('Parcel : ' . $post['webshop_tracking_number'] . ' has been put on-hold.');
            $orderModel->save();
        }
        if ($post['type'] == 'book') {
            $orderModel->setData('book_ind', 1);
            $orderModel->save();

            $shipmentOrders = $this->_orderCollection->create()->addFieldToFilter('shipment_id', $shipmentId);

            $countAllOrders = 0;
            $countArrivedOrders = 0;
            foreach ($shipmentOrders as $rowOrder) {
                if ($rowOrder['entity_id']) {
                    $countAllOrders++;
                }
                if ($rowOrder['book_ind'] == 1) {
                    $countArrivedOrders++;
                }
            }

            if( $shipmentId == 0) {
                $this->messageManager->addWarningMessage('This order is still a draft order, which means that customer did not complete uploading required information yet.');
            } else {
                if ($countArrivedOrders < $countAllOrders) {
                    $this->messageManager->addWarningMessage('Only ' . $countArrivedOrders . ' of the ' . $countAllOrders . ' have arrived.');
                }

                if ($countArrivedOrders == $countAllOrders) {
                    $this->messageManager->addSuccessMessage('All Parcels (' . $countAllOrders . ') have arrived.');
                }
            }


        }
        if ($orderModel->save()) {
            // template variables pass here
            $templateVars = [
                'width' => '2',
                'length' => '3',
                'weight' => '4'
            ];

            $this->helperEmail->sendCustomerEmail(3, $orderModel['customer_id'],$templateVars);



        } else {
            $this->messageManager->addErrorMessage(__('Data was not saved.'));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('wms/orders');
        return $resultRedirect;
    }
}
