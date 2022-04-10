<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * Code standard by : Eos
 */
namespace Eos\Orders\Controller\Adminhtml\Orders;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Eos\Base\Model\OrderFactory;

class Delete extends Action
{
    /**
     * @var OrderFactory
     */
    protected $orderFactory;
    /**
     * @param  Context           $context
     * @param  OrderFactory $orderFactory
     */
    public function __construct(
        Context $context,
        OrderFactory $orderFactory
    ) {
        $this->orderFactory = $orderFactory;
        parent::__construct($context);
    }
    /**
     * For allow to access or not
     *
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Eos_Orders::orders');
    }
    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('entity_id', null);
        try {
            $helloworldData = $this->orderFactory->create()->load($id);
            if ($helloworldData->getId()) {
                $helloworldData->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the record.'));
            } else {
                $this->messageManager->addErrorMessage(__('Record does not exist.'));
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }
        return $resultRedirect->setPath('*/*');
    }
}
