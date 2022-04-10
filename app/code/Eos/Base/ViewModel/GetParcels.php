<?php
namespace Eos\Base\ViewModel;

use Eos\Base\Model\ResourceModel\Parcel\Collection as EntityCollection;
use Eos\Base\Model\ResourceModel\Parcel\CollectionFactory as EntityCollectionFactory;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class GetParcels implements ArgumentInterface
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

    public function getParcels($status = null)
    {
        /** @var $entityCollection entityCollection */
        $entityCollection = $this->entityCollectionFactory->create();
        if ($status) {
            foreach ($status as $key => $value) {
                if ($key != "form_key" && $value != "") {
                    $entityCollection->addFieldToFilter($key, ['like' => "%" . $value . "%"]);
                }
            }
        }

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
