<?php

namespace Eos\Base\ViewModel;

use Eos\Base\Model\ResourceModel\Parcel\CollectionFactory as ParcelCollectionFactory;
use Eos\Base\Helper\ApiCallSF as helperApiCallSF;

class ParcelViewModel extends BaseViewModel
{
    private $parcelCollectionFactory;
    private $helperApiCallSF;
    protected $parcelCollection;

    public function __construct(
        ParcelCollectionFactory $parcelCollectionFactory,
        helperApiCallSF $helperApiCallSF
    ) {
        $this->parcelCollectionFactory = $parcelCollectionFactory;
        $this->helperApiCallSF = $helperApiCallSF;
    }

    public function getParcels()
    {
        if (!$this->parcelCollection) {
            $this->parcelCollection = $this->parcelCollectionFactory->create();
        }
        return $this;
    }

    public function filterById($id)
    {
        return $this->parcelCollection->addFieldToFilter('main_table.entity_id', ['eq' => $id]);
    }

    public function filterByCustomer($customerId)
    {
        return $this->parcelCollection->addFieldToFilter('main_table.customer_id', ['eq' => $customerId]);
    }

    public function filterByInboundAwb($awbCode)
    {
        return $$this->parcelCollection->addFieldToFilter('tracking_number', ['eq' => $awbCode]);
    }

    public function getFirstItem()
    {
        return $this->parcelCollection->getFirstItem();
    }

    public function getItems()
    {
        $this->sortParcels();
        return $this->parcelCollection->getItems();
    }

    public function getSize()
    {
        return $this->parcelCollection->getSize();
    }

    public function sortParcels()
    {
        return $this->parcelCollection->setOrder('main_table.created_at', 'DESC');
    }


}