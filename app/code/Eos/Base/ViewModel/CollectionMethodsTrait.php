<?php
namespace Eos\Base\ViewModel;

trait CollectionMethodsTrait
{
    protected $collection;

    public function setCollection($collection)
    {
        $this->collection = $collection;
        return $this;
    }

    public function getFirstItem()
    {
        return $this->collection->getFirstItem();
    }

    public function getItems()
    {
        return $this->collection->getItems();
    }

    public function getSize()
    {
        return $this->collection->getSize();
    }

    public function filterById($id)
    {
        return $this->collection->addFieldToFilter('main_table.entity_id', ['eq' => $id]);;
    }

    public function filterByCustomer($customerId)
    {
        return $this->collection->addFieldToFilter('main_table.customer_id', ['eq' => $customerId]);
    }

    public function filterByStatus($status)
    {
        $statusImplode = implode(",", $status);
        return $this->collection->addFieldToFilter('main_table.status', ['in' => $statusImplode]);
    }

    public function sortDesc()
    {
       return $this->orderCollection->setOrder('main_table.created_at', 'DESC');
    }
}