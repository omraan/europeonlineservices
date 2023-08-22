<?php

namespace Eos\Base\ViewModel;

use Eos\Base\Model\ResourceModel\Warehouse\CollectionFactory as WarehouseCollectionFactory;

class WarehouseViewModel extends BaseViewModel
{
    private $warehouseCollectionFactory;

    protected $warehouseCollection;

    public function __construct(
        WarehouseCollectionFactory $warehouseCollectionFactory
    ) {
        $this->warehouseCollectionFactory = $warehouseCollectionFactory;
    }

    public function getWarehouses()
    {
        if (!$this->warehouseCollection) {
            $this->warehouseCollection = $this->warehouseCollectionFactory->create();
        }
        return $this;
    }
    public function filterById($id)
    {
        $this->warehouseCollection->addFieldToFilter('entity_id', ['eq' => $id]);
        return $this;
    }

    public function sortWarehouses()
    {
        $this->warehouseCollection->setOrder('title', 'ASC');
    }

    public function getFirstItem()
    {
        return $this->warehouseCollection->getFirstItem();
    }

    public function getItems()
    {
        $this->sortWarehouses();
        return $this->warehouseCollection->getItems();
    }

    public function getSize()
    {
        return $this->warehouseCollection->getSize();
    }
}