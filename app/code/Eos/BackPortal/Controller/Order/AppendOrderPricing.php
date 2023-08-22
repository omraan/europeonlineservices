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
use Magento\Setup\Exception;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection;

class AppendOrderPricing extends \Magento\Framework\App\Action\Action
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

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;


    public function __construct(
        Context $context,
        Session $customerSession,
        DateTime $dateTime,
        OrderFactory $order,
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
        $this->_orderDetails = $_orderDetails;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context);
    }
    public function execute()
    {
        
       // return $this->getRequest()->getParams();
        // Check if user is logged-in
        if ($this->_customerSession->getCustomer()->getId() > 0) {
            $post = $this->getRequest()->getParams();
            foreach($post['products'] as $product) {
                $orderDetailsModel = $this->_orderDetails->create()->load($product['order_details_id']);
                $orderDetailsModel->setData('product_price_net',    floatval($product['product_price_net']) / intval($product['product_amount']));
                $orderDetailsModel->setData('product_price_gross',  floatval($product['product_price_gross']) / intval($product['product_amount']));
                $orderDetailsModel->setData('product_amount',       intval($product['product_amount']));
                $orderDetailsModel->setData('product_tax',          intval($product['product_tax']));
                $orderDetailsModel->save();
            }
            $orderModel = $this->_order->create()->load($post['order_id']);
            $orderModel->setData('warehouse_id', 1);
            $orderModel->setData('webshop_currency', "EUR");
            $orderModel->setData('webshop_order_total_price_net',       floatval($post['webshop_order_total_price_net']));
            $orderModel->setData('webshop_order_total_price_gross',     floatval($post['webshop_order_total_price_gross']));
            $orderModel->setData('webshop_order_costs_price_net',       floatval($post['webshop_order_costs_price_net']));
            $orderModel->setData('webshop_order_costs_price_gross',     floatval($post['webshop_order_costs_price_gross']));
            $orderModel->setData('webshop_order_discount_price_net',    floatval($post['webshop_order_discount_price_net']));
            $orderModel->setData('webshop_order_discount_price_gross',  floatval($post['webshop_order_discount_price_gross']));
            $orderModel->setData('status', 'open:pricing');
            $orderModel->save();

            echo'done';

        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/login');
        }
    }
}
