<?php

namespace Eos\BackPortal\Controller\Order;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\View\Result\Page;

class Create implements HttpGetActionInterface
{
    public function __construct(
        private PageFactory $pageFactory
    ) {}

    public function execute(): Page
    {
        return $this->pageFactory->create();
    }
}
