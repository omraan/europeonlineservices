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

class AppendOrderWebshop extends \Magento\Framework\App\Action\Action
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
        $resultRedirect = $this->resultRedirectFactory->create();

        // Check if user is logged-in
        if ($this->_customerSession->getCustomer()->getId() > 0) {

            $post = $this->getRequest()->getParams();
            $edit = intval($post['edit']);

            $path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('');
            $renamedFileName = $post['webshop_tracking_number'] . ".pdf";
            $targetFile = $path . $renamedFileName;

            if(file_exists($targetFile)){
                $success = true;
            } else{
                $success = $this->uploadPdf($targetFile);
            }


            if($edit > 0) {
                $success = true;
            }

            if ($success) {
                //$mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                //$data['imagename'] = $renamedFileName;

                // Create Order Record
                $orderModel = $this->_order->create()->load(intval($post['order_id']));
                $orderModel->setData('webshop_order_date', date("Y-m-d", strtotime($post['webshop_order_date'])));
                $orderModel->setData('webshop_title', $post['webshop_title']);
                $orderModel->setData('webshop_order_nr', $post['webshop_order_nr']);
                $orderModel->setData('webshop_tracking_number', $post['webshop_tracking_number']);
                $orderModel->setData('status', 'open:webshop');
                $orderModel->save();

                $orderId = $orderModel->getId();
                echo $orderId;
                die();
            } else{
                echo "0";
                die();
            }
        } else {
            $resultRedirect->setPath('customer/account/login');
        }
    }

    function uploadPdf($targetFile) {
        $post = $this->getRequest()->getParams();
        $file = $_FILES['file'];
        $filetype = explode("/", $file['type'])[1];

        if($file['size'] !== 0) {
            if (in_array(strtolower($filetype), ['pdf', 'doc', 'docx'])) {

                if ($file['name'] != '') {

                    $path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('');

                    $renamedFileName = $post['webshop_tracking_number'] . "." . $filetype;
                    $targetFile = $path . $renamedFileName;

                    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                        return true;
                    }else {
                        return false;
                    }
                }
            } else {
                return false;

            }
        }
    }
}
