<?php

namespace Eos\BackPortal\Controller\ToDo;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

use Eos\Base\Helper\ExportBatch;

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
     * @var ExportBatch
     */
    protected $_helperBatch;

    // Instantiating the Context object is no longer required
    public function __construct(
        PageFactory $pageFactory,
        ExportBatch $helperBatch
    ) {
        // Calling parent::__construct() is also no longer needed
        $this->pageFactory = $pageFactory;
        $this->_helperBatch = $helperBatch;
    }

    public function execute()
    {

        $this->_helperBatch->ExportJson(1);

    }
}
