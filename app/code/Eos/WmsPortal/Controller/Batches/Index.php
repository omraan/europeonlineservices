<?php

namespace Eos\WmsPortal\Controller\Batches;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

// Composition implements an action interface. Common interfaces to implement:
// Create - HttpPutActionInterface
// Read - HttpGetActionInterface
// Update - HttpPostActionInterface
// Delete - HttpDeleteActionInterface
class Index implements HttpGetActionInterface
{
    /** @var PageFactory */
    private $pageFactory;


    // Instantiating the Context object is no longer required
    public function __construct(
        PageFactory $pageFactory
    ) {
        // Calling parent::__construct() is also no longer needed
        $this->pageFactory = $pageFactory;
    }

    public function execute()
    {

        return $this->pageFactory->create();
    }
}
