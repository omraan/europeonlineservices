<?php

namespace Eos\WmsPortal\Controller\Orders;

use Eos\Base\Model\ShipmentFactory;
use Eos\Base\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Eos\Base\Model\ResourceModel\ParcelTemplate\CollectionFactory as ParcelTemplateCollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use \Magento\Customer\Api\CustomerRepositoryInterface;
use Eos\Base\Helper\Email;

class Save extends \Magento\Framework\App\Action\Action
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
     * @var OrderCollectionFactory
     */

    protected $_orderCollection;

    /**
     * @var ParcelTemplateCollectionFactory
     */

    protected $_parcelTemplateCollection;

    /** @var CustomerRepositoryInterface */
    protected $_customerRepositoryInterface;

    /** @var Email */

    protected $helperEmail;

    public function __construct(
        Context $context,
        Session $customerSession,
        ShipmentFactory $shipment,
        OrderCollectionFactory $orderCollection,
        ParcelTemplateCollectionFactory $parcelTemplateCollection,
        Email $helperEmail
    ) {
        $this->_customerSession = $customerSession;
        $this->_shipment = $shipment;
        $this->_orderCollection = $orderCollection;
        $this->_parcelTemplateCollection = $parcelTemplateCollection;
        $this->helperEmail = $helperEmail;
        parent::__construct($context);
    }
    public function execute()
    {
        $post = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();

        // Check if user is logged-in
        if ($this->_customerSession->getCustomer()->getId() > 0) {
            // Create Order Record
            $shipmentModel = $this->_shipment->create()->load($post['outbound_tracking_number'] , 'awb_code');

            if(isset($post['total_weight'])) {

                $weight = $post['total_weight'];

            }
            $string = "parcelTemplate-";

            foreach ($post as $key => $value) {
                if (substr($key, 0, strlen($string)) == $string) {
                    $consolidation = true;
                    $entity_id = explode('-', $key)[1];
                }
            }
            $weight = 0;

            if(isset($consolidation)) {
                $parcelTemplate = $this->_parcelTemplateCollection->create()->addFieldToFilter("entity_id" , $entity_id)->getFirstItem();
                $weightDimensions = ( $parcelTemplate['length'] * $parcelTemplate['width'] * $parcelTemplate['height'] ) / 5000;

                $weight = $weightDimensions;
            }



            if(isset($post['total_weight'])) {

                if(isset($consolidation)) {
                    $weight = $post['total_weight'] > $weightDimensions ? $post['total_weight'] : $weightDimensions;
                } else {
                    $weight = $post['total_weight'];
                }

            }

            $shipmentModel->setData('total_weight', floatval($weight));
            $shipmentModel->setData('status', 'Ready for payment');
            $shipmentModel->save();

            if ($shipmentModel->save()) {

                $pricePerUnit = 2.70;
                $order = $this->_orderCollection->create()->addFieldToFilter('shipment_id', $shipmentModel['entity_id'])->getFirstItem();
                $templateVars['reference_code'] = $shipmentModel['f_shipment_id'];
                $templateVars['webshop_title'] = $order['webshop_title'];
                $templateVars['price'] = number_format(15 + ($pricePerUnit * $weight),2, ',', '.');

                $this->helperEmail->sendCustomerEmail(4, $shipmentModel['customer_id'],$templateVars);


                $this->messageManager->addSuccessMessage(__('You saved the data.'));
            } else {
                $this->messageManager->addErrorMessage(__('Data was not saved.'));
            }

            $resultRedirect->setPath('wms/orders');
        } else {
            $resultRedirect->setPath('customer/account/login');
        }

        return $resultRedirect;
    }
}
