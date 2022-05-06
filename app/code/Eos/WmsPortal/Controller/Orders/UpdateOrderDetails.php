<?php

namespace Eos\WmsPortal\Controller\Orders;

use Eos\Base\Model\OrderDetailsFactory;
use Eos\Base\Model\HsFactory;
use Eos\Base\Model\HsProductFactory;
use Eos\Base\Model\ResourceModel\Hs\Collection as HsCollection;
use Eos\Base\Model\ResourceModel\Hs\CollectionFactory as HsCollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Setup\Exception;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection;

class UpdateOrderDetails extends \Magento\Framework\App\Action\Action
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
     * @var HsFactory
     */

    protected $_hs;

    /**
     * @var HsCollectionFactory
     */
    protected $_hsCollectionFactory;

    /**
     * @var HsProductFactory
     */

    protected $_hsProduct;

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
        HsFactory $hs,
        HsCollectionFactory $hsCollectionFactory,
        HsProductFactory $hsProduct,
        OrderDetailsFactory $_orderDetails,
        UploaderFactory $fileUploaderFactory,
        DirectoryList $directory_list,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        ResourceConnection $resourceConnection
    ) {
        $this->_customerSession = $customerSession;
        $this->_dateTime = $dateTime;
        $this->_hs = $hs;
        $this->_hsCollectionFactory = $hsCollectionFactory;
        $this->_hsProduct = $hsProduct;
        $this->_orderDetails = $_orderDetails;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context);
    }
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $post = $this->getRequest()->getParams();

        $orderDetails_id = intval($post['post'][0]['orderdetails_id']);

        $orderDetailsModel = $this->_orderDetails->create()->load($orderDetails_id);
        $sizeof_productnr = $this->_hsCollectionFactory->create()->addFieldToFilter('main_table.product_tax_nr', ['eq' => $post['post'][0]['product_tax_nr']])->getSize();
        $sizeof_hsode = $this->_hsCollectionFactory->create()->addFieldToFilter('main_table.hs_code', ['eq' => $post['post'][0]['hs_code']])->getSize();

        if($sizeof_productnr === 0 && $sizeof_hsode > 0) {
            echo "Not saved because the 'Product Tax nr' does not exist, while HS code does exist. Please refresh page. If this problem still occurs, please contact Administrator";
            die();
        } else {
            $orderDetailsModel->setData('product_tax_nr', $post['post'][0]['product_tax_nr']);
        }

        $orderDetailsModel->setData('product_title', $post['post'][0]['product_title']);
        $orderDetailsModel->setData('product_amount', $post['post'][0]['product_amount']);
        $orderDetailsModel->setData('product_type', $post['post'][0]['product_type']);
        $orderDetailsModel->setData('product_price_gross', $post['post'][0]['product_price_gross']);
        $orderDetailsModel->setData('product_price_net', $post['post'][0]['product_price_net']);
        $orderDetailsModel->setData('product_tax', $post['post'][0]['product_tax']);

        if($sizeof_hsode > 0) {
            /** @var $hsCollection HsCollectionFactory  */
            $hsCollection = $this->_hsCollectionFactory->create()->addFieldToFilter('main_table.hs_code', ['eq' => $post['post'][0]['hs_code']]);

            $orderDetailsModel->setData('product_tax_nr', $hsCollection->getFirstItem()['product_tax_nr']);

            $orderDetailsModel->save();

            echo "Done.";
            die();
        } else {

            // Check whether product_tax_nr is existing, cause we don't want to override existing records
            if($sizeof_productnr > 0) {
                echo "Not saved because the new HS code is based on an existing 'Product Tax nr'. Please make sure the product tax nr is unique";
                die();
            } else {
                $hsModel = $this->_hs->create();
                $hsModel->setData('product_tax_nr' , $post['post'][0]['product_tax_nr']);
                $hsModel->setData('hs_code' , $post['post'][0]['hs_code']);
                $hsModel->save();

                $hsProductModel = $this->_hsProduct->create();
                $hsProductModel->setData('product_tax_nr' , $post['post'][0]['product_tax_nr']);
                $hsProductModel->setData('product_title' , $post['post'][0]['product_title']);
                $hsProductModel->setData('lang' , 'en');
                $hsProductModel->save();

                $orderDetailsModel->save();

                echo "Changes are saved";
            }

        }

    }
}
