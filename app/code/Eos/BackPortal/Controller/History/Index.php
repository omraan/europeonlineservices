<?php

namespace Eos\BackPortal\Controller\History;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Eos\Base\Model\ResourceModel\Order\Collection as OrderCollection;
use Eos\Base\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

// Composition implements an action interface. Common interfaces to implement:
// Create - HttpPutActionInterface
// Read - HttpGetActionInterface
// Update - HttpPostActionInterface
// Delete - HttpDeleteActionInterface
class Index implements HttpGetActionInterface
{
    /** @var PageFactory */
    private $pageFactory;

    /** @var OrderCollectionFactory */

    protected $orderCollectionFactory;

    // Instantiating the Context object is no longer required
    public function __construct(
        PageFactory $pageFactory,
        OrderCollectionFactory $orderCollectionFactory
    ) {
        // Calling parent::__construct() is also no longer needed
        $this->pageFactory = $pageFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    public function execute()
    {

        $orderCollection = $this->orderCollectionFactory->create();
        $firstItem = $orderCollection->getFirstItem();
        $allItems = $orderCollection->getItems();

        return $this->pageFactory->create();
    }
}
