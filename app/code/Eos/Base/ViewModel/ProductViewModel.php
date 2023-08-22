<?php

namespace Eos\Base\ViewModel;

use Eos\Base\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class ProductViewModel extends BaseViewModel
{
    private $productCollectionFactory;

    protected $productCollection;

    public function __construct(
        ProductCollectionFactory $productCollectionFactory
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
    }

    public function getProducts()
    {
        if (!$this->productCollection) {
            $this->productCollection = $this->productCollectionFactory->create();
        }
        return $this;
    }
    public function getDistinctValues($column)
    {
        return $this->productCollection->getDistinctValues($column);
    }

    public function filterById($id)
    {
        $this->productCollection->addFieldToFilter('entity_id', ['eq' => $id]);
        return $this;
    }

    public function sortProducts()
    {
        $this;
    }

    public function getFirstItem()
    {
        return $this->productCollection->getFirstItem();
    }

    public function getItems()
    {
        $this->sortProducts();
        return $this->productCollection->getItems();
    }

    public function getSize()
    {
        return $this->productCollection->getSize();
    }
}