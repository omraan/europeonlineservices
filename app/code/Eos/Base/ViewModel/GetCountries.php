<?php
namespace Eos\Base\ViewModel;

use Eos\Base\Model\ResourceModel\Country\Collection as EntityCollection;
use Eos\Base\Model\ResourceModel\Country\CollectionFactory as EntityCollectionFactory;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class GetCountries implements ArgumentInterface
{

    /**
     * @var entityCollectionFactory
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

    public function getCountries($type, $lang = 'en')
    {
        /** @var $entityCollection entityCollection */
        $entityCollection = $this->entityCollectionFactory->create();
        $entityCollection->addFieldToFilter('country_type', ['like' => $type]);
        $entityCollection->addFieldToFilter('country_lang', ['like' => $lang]);
        return $entityCollection->getItems();
    }

    /**
     * Get message for no orders.
     *
     * @return Phrase
     * @since 102.1.0
     */
    public function getEmptyItemsMessage()
    {
        return __('There are no orders placed or your parcel(s) have not arrived yet.');
    }
}
