<?php
namespace Eos\Base\ViewModel;

use Eos\Base\Model\ResourceModel\Shipment\CollectionWithOrder as EntityCollection;
use Eos\Base\Model\ResourceModel\Shipment\CollectionWithOrderFactory as EntityCollectionFactory;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class GetCustomerShipments implements ArgumentInterface
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

    public function getCustomerId()
    {
        $customer = $this->customerSession->create();
        var_dump($customer);
    }

    public function getShipments($status = null, $shipment = false)
    {
        $customerId = $this->customerSession->create()->getCustomer()->getId();


        if (!($customerId)) {
            return false;
        }

        /** @var $entityCollection EntityCollection */
        if ($status) {
            $entityCollection = $this->entityCollectionFactory->create()->addFieldToFilter('main_table.customer_id', $customerId)->addFieldToFilter('main_table.status', $status);
        } else {
            $entityCollection = $this->entityCollectionFactory->create()->addFieldToFilter('main_table.customer_id', $customerId);
        }
        if ($shipment > 1) {
            $entityCollection->addFieldToFilter('main_table.entity_id', ['eq' => $shipment]);
        } else {
            $entityCollection->getSelect()->group('main_table.entity_id');
        }

        return $entityCollection->getItems();
    }



    /**
         * Get message for no shipments.
         *
         * @return Phrase
         * @since 102.1.0
         */
    public function getEmptyShipmentMessage()
    {
        return __('You have placed no shipments.');
    }
}
