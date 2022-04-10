<?php

namespace Eos\FrontPortal\Controller\Howitworks;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Request\Http;
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


    /** @var Http */
    protected $_request;

    // Instantiating the Context object is no longer required
    public function __construct(
        PageFactory $pageFactory,
        Http $request

    ) {
        // Calling parent::__construct() is also no longer needed
        $this->pageFactory = $pageFactory;
        $this->_request = $request;
    }

    public function execute()
    {
        echo '<pre>';
        var_dump(($this->_request->getPathInfo()));
        die();
        return $this->pageFactory->create();
    }
}
