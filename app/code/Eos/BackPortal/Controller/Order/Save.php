<?php

namespace Eos\BackPortal\Controller\Order;

use Eos\Base\Model\OrderDetailsFactory;
use Eos\Base\Model\OrderFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;

class Save extends \Magento\Framework\App\Action\Action
{

    /**
     * @var Session
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
     * @var OrderDetailsFactory
     */
    protected $_orderDetails;

    /**
     * @var UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @var DirectoryList
     */
    protected $_directory_list;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    public function __construct(
        Context $context,
        Session $customerSession,
        DateTime $dateTime,
        OrderFactory $order,
        OrderDetailsFactory $_orderDetails,
        UploaderFactory $fileUploaderFactory,
        DirectoryList $directory_list,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager
    ) {
        $this->_customerSession = $customerSession;
        $this->_dateTime = $dateTime;
        $this->_order = $order;
        $this->_orderDetails = $_orderDetails;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_directory_list = $directory_list;
        $this->_storeManager = $storeManager;
        $this->_filesystem = $filesystem;
        parent::__construct($context);
    }
    public function execute()
    {
        $post = $this->getRequest()->getParams();
        $success = false;
        $extError = true;
        $responseArray = [];

        $file = $_FILES['imagename'];
        $filetype = explode("/", $file['type'])[1];


        if (in_array(strtolower($filetype), ['pdf', 'doc', 'docx'])) {

            if ($file['name'] != '') {

                $path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('');

                $renamedFileName = $post['webshop_tracking_number'] . "." . $filetype;
                $targetFile = $path . '/' . $renamedFileName;

                if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                    $success = true;
                }


            }
        } else {
            $extError = false;
            $message = __('Please upload only PDF, DOC, DOCX.');


        }

        if ($success) {
            $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $data['imagename'] = $renamedFileName;

        }

        $resultRedirect = $this->resultRedirectFactory->create();

        // Check if user is logged-in
        if ($this->_customerSession->getCustomer()->getId() > 0) {
            $order = $this->_order->create();

            // Create Order Record
            $orderModel = $this->_order->create();
            $orderModel->setData('customer_id', $this->_customerSession->getCustomer()->getId());
            $orderModel->setData('webshop_currency', $post['webshopCurrency']);
            $orderModel->setData('webshop_title', $post['webshop_title']);
            $orderModel->setData('webshop_order_nr', $post['webshop_order_nr']);
            $orderModel->setData('warehouse_id', $post['warehouse']);
            $orderModel->setData('webshop_tracking_number', $post['webshop_tracking_number']);
            $orderModel->setData('status', 'open');
            $orderModel->setData('book_ind', '0');
            $string = "price_";
            $orderPrice = 0;
            foreach ($post as $key => $value) {
                if (substr($key, 0, strlen($string)) == $string) {
                    $id = explode('_', $key)[1];
                    $target = 'count_' . $id;

                    $orderPrice += floatval($post[$target]) * floatval($value);

                }
            }
            $orderModel->setData('webshop_order_price', $orderPrice);

            // TODO: Remove webshop_order_price & webshop_order_product_amount.

            $orderModel->save();

            $orderId = $orderModel->getId();

            $matchTrackingNumber = $this->_order->create()->load($post['webshop_tracking_number'], 'webshop_tracking_number')['webshop_tracking_number'];
            //        if($matchTrackingNumber) {
            //            $this->messageManager->addErrorMessage(__('Data was not saved.' .$matchTrackingNumber));
            //        } else {

            // Each row in form has <input type=hidden name="counter_{row}" /> This makes sure that the right amount will be looped in foreach
            $string = "counter";
            foreach ($post as $key => $value) {
                if (substr($key, 0, strlen($string)) == $string) {

                    // Create OrderDetails Record
                    $orderDetailsModel = $this->_orderDetails->create();

                    $orderDetailsId = explode("_", $key)[1];

                    $orderDetailsModel->setData('order_id', $orderId);
                    $orderDetailsModel->setData('product_tax_nr', $post['productGroup_' . $orderDetailsId]);
                    $orderDetailsModel->setData('brand', $post['brand_' . $orderDetailsId]);
                    $orderDetailsModel->setData('price', $post['price_' . $orderDetailsId]);
                    $orderDetailsModel->setData('amount', $post['amount_' . $orderDetailsId]);
                    $orderDetailsModel->setData('count', $post['count_' . $orderDetailsId]);

                    $orderDetailsModel->save();
                }
            }

            if ($order->save()) {
                $this->messageManager->addSuccessMessage(__('You saved the data.'));
            } else {
                $this->messageManager->addErrorMessage(__('Data was not saved.'));
            }
            //        }

            $resultRedirect->setPath('orders/shipment/create');
        } else {
            $resultRedirect->setPath('customer/account/login');
        }

        return $resultRedirect;
    }
}
