<?php

namespace Eos\BackPortal\Controller\Order;

use Eos\Base\Model\OrderDetailsFactory;
use Eos\Base\Model\OrderFactory;
use Eos\Base\Model\ResourceModel\Order\Collection as OrderCollection;
use Eos\Base\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Setup\Exception;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection;

class AppendOrderProduct extends \Magento\Framework\App\Action\Action
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
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

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

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;


    public function __construct(
        Context $context,
        Session $customerSession,
        DateTime $dateTime,
        OrderFactory $order,
        OrderCollectionFactory $orderCollectionFactory,
        OrderDetailsFactory $_orderDetails,
        UploaderFactory $fileUploaderFactory,
        DirectoryList $directory_list,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        ResourceConnection $resourceConnection
    ) {
        $this->_customerSession = $customerSession;
        $this->_dateTime = $dateTime;
        $this->_order = $order;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_orderDetails = $_orderDetails;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context);
    }
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        // Check if user is logged-in
        if ($this->_customerSession->getCustomer()->getId() > 0) {
            $post = $this->getRequest()->getParams();
            $order_id = intval($post['general'][0]['order_id']);

            $connection  = $this->resourceConnection->getConnection();
            $tableName = $connection->getTableName('eos_order_details');

            $whereConditions = [
                $connection->quoteInto('order_id = ?', $order_id),
            ];

            $connection->delete($tableName, $whereConditions);
            $orderDetailsArrayId = [];

            $exceedRmb = false;
            $totalNetPrice = 0;

            for($i=0;$i<count($post['product']);$i++) {
                $product = $post['product'][$i];
                $orderDetailsModel = $this->_orderDetails->create();
                $orderDetailsModel->setData('order_id',             $order_id);
                $orderDetailsModel->setData('product_brand',        $product['product_brand']);
                $orderDetailsModel->setData('product_title',        $product['product_title']);
                $orderDetailsModel->setData('product_tax_nr',       intval($product['product_code']));
                $orderDetailsModel->setData('product_amount',       intval($product['product_amount']));
                $orderDetailsModel->setData('product_price_net',    floatval($product['product_price_net']) / intval($product['product_amount']));
                $orderDetailsModel->setData('product_price_gross',  floatval($product['product_price_gross']) / intval($product['product_amount']));
                $orderDetailsModel->setData('product_type',         $product['product_type']);
                $orderDetailsModel->setData('product_tax',          intval($product['product_tax']));

                $totalNetPrice = $totalNetPrice + floatval($product['product_price_net']);


                if($totalNetPrice > 136) {
                    $exceedRmb = true;
                }

                try{
                    $orderDetailsModel->save();
                    array_push($orderDetailsArrayId, $orderDetailsModel->getId());

                }catch (Exception $error) {
                    echo $error;
                    die();
                }
            }

            if($exceedRmb) {

                /** @var $orderCollection OrderCollection */
                $orderCollection = $this->_orderCollectionFactory->create();
                $orderCollection->addFieldToFilter

                $orderFirstItem = $orderCollection->getFirstItem();

                for($c_orderDetails = 0; $c_orderDetails < count($orderDetailsArrayId); $c_orderDetails++) {

                    // Skip first Orderdetails record
                    if($c_orderDetails !== 0) {

                        $orderModel = $this->_order->create();
                        $orderModel->setData('customer_id', $orderFirstItem['customer_id']);
                        $orderModel->setData('warehouse_id', $orderFirstItem['warehouse_id']);
                        $orderModel->setData('webshop_currency', $orderFirstItem['webshop_currency']);
                        $orderModel->setData('webshop_title', $orderFirstItem['webshop_title']);
                        $orderModel->setData('webshop_order_nr', $orderFirstItem['webshop_order_nr']);
                        $orderModel->setData('webshop_tracking_number', $orderFirstItem['webshop_tracking_number']);
                        $orderModel->setData('status', 'open:product');
                        $orderModel->save();

                        $orderDetailsModel = $this->_orderDetails->create()->load($orderDetailsArrayId[$c_orderDetails]);
                        $orderDetailsModel->setData('order_id', $orderModel->getId());
                        $orderDetailsModel->save();

                    } else {

                        $orderModel = $this->_order->create()->load($order_id);
                        $orderModel->setData('status', 'open:product');
                        $orderModel->save();

                    }
                }
            } else {
                $orderModel = $this->_order->create()->load($order_id);
                $orderModel->setData('status', 'open:product');
                $orderModel->save();
            }

            echo json_encode($orderDetailsArrayId);
            die();

        } else {
            $resultRedirect->setPath('customer/account/login');
        }
    }
}
