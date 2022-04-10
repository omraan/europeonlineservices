<?php

namespace Eos\WmsPortal\Controller\Batches;

use Eos\Base\Model\BatchFactory;
use Eos\Base\Model\BatchPalletFactory;
use Eos\Base\Model\Shipment;
use Eos\Base\Model\ShipmentFactory;
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
     * @var BatchFactory
     */

    protected $_batch;

    /**
     * @var BatchPalletFactory
     */

    protected $_batchPallet;

    /**
     * @var ShipmentFactory
     */

    protected $_shipment;

    public function __construct(
        Context $context,
        SessionFactory $customerSession,
        DateTime $dateTime,
        BatchFactory $batch,
        BatchPalletFactory $batchPallet,
        ShipmentFactory $shipment
    ) {
        $this->_customerSession = $customerSession;
        $this->_dateTime = $dateTime;
        $this->_batch = $batch;
        $this->_batchPallet = $batchPallet;
        $this->_shipment = $shipment;
        parent::__construct($context);
    }
    public function execute()
    {
        $post = $this->getRequest()->getParams();


        $batchPalletModel = $this->_batchPallet->create();
        $batchPalletModel->load($post['batch_id'], 'batch_id');

        $shipmentModel = $this->_shipment->create();
        $shipmentModel->load($batchPalletModel->getData('awb_code'), 'awb_code');
        $shipmentModel->setData('status', 'Payed');
        $shipmentModel->save();

        $batchPalletModel->delete();

        $batchModel = $this->_batch->create();
        $batchModel->load($post['batch_id']);
        $batchModel->delete();

        if ($batchModel->delete()) {
            $this->messageManager->addSuccessMessage(__('You succesfully deleted this record.'));
        } else {
            $this->messageManager->addErrorMessage(__('Delete went unsuccesful.'));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('wms/batches');
        return $resultRedirect;
    }
}
