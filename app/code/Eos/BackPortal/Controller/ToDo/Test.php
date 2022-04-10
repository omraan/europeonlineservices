<?php

namespace Eos\BackPortal\Controller\ToDo;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

use Eos\Base\Model\Order\Pdf\Invoicepdf;
use Eos\Base\Model\Order\Pdf\InvoicepdfFactory;

// Composition implements an action interface. Common interfaces to implement:
// Create - HttpPutActionInterface
// Read - HttpGetActionInterface
// Update - HttpPostActionInterface
// Delete - HttpDeleteActionInterface
class Test implements HttpGetActionInterface
{
    /** @var PageFactory */
    private $pageFactory;

    /**
     * @var InvoicepdfFactory
     */
    protected $_invoiceFactory;

    // Instantiating the Context object is no longer required
    public function __construct(
        PageFactory $pageFactory,
        InvoicepdfFactory $invoiceFactory
    ) {
        // Calling parent::__construct() is also no longer needed
        $this->pageFactory = $pageFactory;
        $this->_invoiceFactory = $invoiceFactory;
    }

    public function execute()
    {/** @var Invoicepdf $invoice */
        $this->_invoiceFactory->create()->getPdf();
        return $this->pageFactory->create();
    }
}
