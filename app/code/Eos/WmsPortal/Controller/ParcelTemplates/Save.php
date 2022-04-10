<?php

namespace Eos\WmsPortal\Controller\ParcelTemplates;

use Eos\Base\Model\ParcelTemplateFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;

class Save extends \Magento\Framework\App\Action\Action
{

    /**
     * @var ParcelTemplateFactory
     */

    protected $_parcelTemplate;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    public function __construct(
        Context $context,
        ParcelTemplateFactory $parcelTemplate,
        ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->_parcelTemplate = $parcelTemplate;
        $this->messageManager = $messageManager;
    }
    public function execute()
    {
        $post = $this->getRequest()->getParams();
        $parcelTemplateModel = $this->_parcelTemplate->create();
        $parcelTemplateModel->setData('title', $post['title']);
        $parcelTemplateModel->setData('width', $post['width']);
        $parcelTemplateModel->setData('height', $post['height']);
        $parcelTemplateModel->setData('length', $post['length']);
        $parcelTemplateModel->save();

        if ($parcelTemplateModel->save()) {
            $this->messageManager->addSuccessMessage(__('You saved the data.'));
        } else {
            $this->messageManager->addErrorMessage(__('Data was not saved.'));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($post['redirect']);
        return $resultRedirect;
    }
}
