<?php
namespace Eos\Base\ViewModel;

use Eos\Base\Model\ResourceModel\Order\Collection as EntityCollection;
use Eos\Base\Model\ResourceModel\Order\CollectionFactory as EntityCollectionFactory;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class GetCustomerOrders implements ArgumentInterface
{

    /**
     * @var EntityCollectionFactory
     */
    protected $entityCollectionFactory;

    /**
     * @var SessionFactory
     */
    protected $customerSession;

    public function __construct(
        EntityCollectionFactory $entityCollectionFactory,
        SessionFactory $customerSession
    ) {
        $this->entityCollectionFactory = $entityCollectionFactory;
        $this->customerSession = $customerSession;
    }

    public function getOrders($status = null, $shipment = false)
    {
        $customerId = $this->customerSession->create()->getCustomer()->getId();

        if (!($customerId)) {
            return false;
        }

        /** @var $entityCollection EntityCollection */
        if ($status) {
            $entityCollection = $this->entityCollectionFactory->create()->addFieldToFilter('customer_id', $customerId)->addFieldToFilter('status', $status);
        } else {
            $entityCollection = $this->entityCollectionFactory->create()->addFieldToFilter('customer_id', $customerId);
        }
        if ($shipment == 1) {
            $entityCollection->addFieldToFilter('shipment_id', ['neq' => 0]);
        } elseif ($shipment > 1) {
            $entityCollection->addFieldToFilter('shipment_id', ['eq' => $shipment]);
        } else {
            $entityCollection->addFieldToFilter('shipment_id', ['eq' => 0]);
        }

        return $entityCollection->getItems();
    }

    /**
     * Get message for no orders.
     *
     * @return Phrase
     * @since 102.1.0
     */
    public function getEmptyOrdersMessage()
    {
        return __('There are no orders placed or your parcel(s) have not arrived yet.');
    }
}
