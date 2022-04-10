<?php

namespace Eos\BackPortal\Controller\Support;

use Eos\Base\Model\OrderDetailsFactory;
use Eos\Base\Model\OrderFactory;
use Eos\Base\Model\Pdf\Invoice;
use Eos\Base\Model\Pdf\InvoiceFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

class Test implements HttpGetActionInterface
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
     * @var InvoiceFactory
     */
    protected $_invoiceFactory;

    /** @var PageFactory */
    private $pageFactory;

    public function __construct(
        Session $customerSession,
        DateTime $dateTime,
        OrderFactory $order,
        OrderDetailsFactory $_orderDetails,
        UploaderFactory $fileUploaderFactory,
        DirectoryList $directory_list,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        InvoiceFactory $invoiceFactory,
        PageFactory $pageFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_dateTime = $dateTime;
        $this->_order = $order;
        $this->_orderDetails = $_orderDetails;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_directory_list = $directory_list;
        $this->_storeManager = $storeManager;
        $this->_filesystem = $filesystem;
        $this->_invoiceFactory = $invoiceFactory;
        $this->pageFactory = $pageFactory;
    }
    public function execute()
    {
        /** @var Invoice $invoice */

        sprintf($this->_invoiceFactory->create()->getPdf());
        return $this->pageFactory->create();

    }
}
