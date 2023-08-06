<?php

namespace Eos\BackPortal\Controller\ToDo;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Eos\Base\Helper\ApiCallSF;

// Composition implements an action interface. Common interfaces to implement:
// Create - HttpPutActionInterface
// Read - HttpGetActionInterface
// Update - HttpPostActionInterface
// Delete - HttpDeleteActionInterface
class Index implements HttpGetActionInterface
{
    /** @var PageFactory */
    private $pageFactory;

    /** @var ApiCallSF */
    private $apiCallSF;

    // Instantiating the Context object is no longer required
    public function __construct(
        PageFactory $pageFactory,
        ApiCallSF $apiCallSF,
    ) {
        // Calling parent::__construct() is also no longer needed
        $this->pageFactory = $pageFactory;
        $this->apiCallSF = $apiCallSF;
    }

    public function execute()
    {
        // $test = $this->apiCallSF->sfCreateShipment();
        // print_r($test);
        // die();
        return $this->pageFactory->create();
    }
}
