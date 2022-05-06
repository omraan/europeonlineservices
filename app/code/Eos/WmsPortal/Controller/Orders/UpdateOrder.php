<?php

namespace Eos\WmsPortal\Controller\Orders;

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

class UpdateOrder extends \Magento\Framework\App\Action\Action
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
        $post = $this->getRequest()->getParams();
        $order_id = intval($post['post'][0]['order_id']);

        $orderModel = $this->_order->create()->load($order_id);
        //$orderModel->setData('warehouse_id', $post['post'][0]['warehouse_id']);
        //$orderModel->setData('webshop_currency', $post['post'][0]['webshop_currency']);
        $orderModel->setData('webshop_title', $post['post'][0]['webshop_title']);
        $orderModel->setData('webshop_order_nr', $post['post'][0]['webshop_order_nr']);
        $orderModel->setData('status', $post['post'][0]['status']);
        $orderModel->setData('webshop_tracking_number', $post['post'][0]['webshop_tracking_number']);
        $orderModel->setData('webshop_order_discount_price_net', $post['post'][0]['webshop_order_discount_price_net']);
        $orderModel->setData('webshop_order_costs_price_net', $post['post'][0]['webshop_order_costs_price_net']);
        $orderModel->setData('webshop_order_total_price_net', $post['post'][0]['webshop_order_total_price_net']);
        $orderModel->setData('webshop_tax', $post['post'][0]['webshop_tax']);
        $orderModel->save();

        echo "Done";

    }
}
