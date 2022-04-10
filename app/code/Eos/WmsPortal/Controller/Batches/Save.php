<?php

namespace Eos\WmsPortal\Controller\Batches;

use Eos\Base\Model\OrderFactory;
use Eos\Base\Model\ParcelFactory;
use Eos\Base\Model\BatchFactory;
use Eos\Base\Model\BatchPalletFactory;
use Eos\Base\Model\ResourceModel\Batch\CollectionWithPallets as BatchPalletCollection;
use Eos\Base\Model\ResourceModel\Batch\CollectionWithPalletsFactory as BatchPalletCollectionFactory;
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
     * @var BatchFactory
     */

    protected $_batch;

    /**
     * @var BatchPalletFactory
     */

    protected $_batchPallet;

    /**
     * @var BatchPalletCollectionFactory
     */
    protected $batchPalletCollectionFactory;

    /**
     * @var ParcelFactory
     */

    protected $_parcel;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    public function __construct(
        Context $context,
        SessionFactory $customerSession,
        DateTime $dateTime,
        ShipmentFactory $shipment,
        BatchFactory $batch,
        BatchPalletFactory $batchPalletFactory,
        BatchPalletCollectionFactory $batchPalletCollectionFactory,
        ParcelFactory $parcel,
        ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->_dateTime = $dateTime;
        $this->_shipment = $shipment;
        $this->_batch = $batch;
        $this->_batchPallet = $batchPalletFactory;
        $this->batchPalletCollectionFactory = $batchPalletCollectionFactory;
        $this->_parcel = $parcel;
        $this->messageManager = $messageManager;
    }
    public function execute()
    {
        $post = $this->getRequest()->getParams();
        $customerId = $this->_customerSession->create()->getCustomer()->getId();

        $batchModel = $this->_batch->create();

        if(isset($post['batch_id'])) {
            $batchModel->load($post['batch_id']);

            if(isset($post['mawb_code'])) {
                $batchModel->setData('mawb_code', $post['mawb_code']);
                $batchModel->setData('status','Dispatched');
            }
            if(isset($post['flight_code'])) {
                $batchModel->setData('flight_code', $post['flight_code']);
            }

            // Each adjustment to a batch will force a clean-up on BatchPallet Collection.
            // This is because only data from $post related to BatchPallet is seen as the 'truth'.

            /** @var $batchPalletCollection BatchPalletCollection */
            $batchPalletCollection = $this->batchPalletCollectionFactory->create();
            $batchPalletCollection->addFieldToFilter('batch_id', ['eq' => $post['batch_id']]);
            $amountPallets =  $batchPalletCollection->getSize();

            for($i=0;$i<$amountPallets;$i++) {
                $batchPalletModel = $this->_batchPallet->create()->load($post['batch_id'] , 'batch_id');
                $shipmentModel = $this->_shipment->create()->load($batchPalletModel->getData('awb_code'), 'awb_code');
                $shipmentModel->setData('status', 'Payed');
                $shipmentModel->save();

                $batchPalletModel->delete()->save();
            }
        }

        $batchModel->setData('warehouse_id', $post['warehouse']);
        $batchModel->setData('user_id', $customerId);
        $batchModel->setData('vehicle_tag', $post['vehicle_tag']);
        $batchModel->setData('status', $post['status_placeholder']);
        $batchModel->save();

        $batchId = $batchModel->getId();
        $batchCode = "EOSB" . str_pad($batchId, 6, "0", STR_PAD_LEFT);
        $batchModel->setData('batch_code', $batchCode);
        $batchModel->save();

        // Loop through all awb_{$awbCode} like "awb_SF1323636221206"
        $string = "awb_";
        foreach ($post as $key => $palletCode) {
            if (substr($key, 0, strlen($string)) == $string) {

                $awbCode = explode('_',$key)[1];
                $batchPalletModel = $this->_batchPallet->create();
                $batchPalletModel->setData('batch_id', $batchId);
                $batchPalletModel->setData('awb_code', $awbCode);
                $batchPalletModel->setData('pallet_code', $palletCode);
                $batchPalletModel->save();

                if($palletCode) {
                    $shipmentModel = $this->_shipment->create()->load($awbCode, 'awb_code');
                    $shipmentModel->setData('status', 'In Batch');
                    $shipmentModel->save();
                }


            }
        }

        if ($shipmentModel->save()) {
            $this->messageManager->addSuccessMessage(__('You saved the data.'));
        } else {
            $this->messageManager->addErrorMessage(__('Data was not saved.'));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('wms/batches');
        return $resultRedirect;
    }
}
