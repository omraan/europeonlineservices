<?php
namespace Eos\Base\ViewModel;

use Eos\Base\Model\ResourceModel\Hs\Collection as ProductGroupCollection;
use Eos\Base\Model\ResourceModel\Hs\CollectionFactory as ProductGroupCollectionFactory;
use Eos\Base\Model\ResourceModel\Warehouse\Collection as WarehouseCollection;
use Eos\Base\Model\ResourceModel\Warehouse\CollectionFactory as WarehouseCollectionFactory;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class GetProductGroup implements ArgumentInterface
{

    /**
     * @var ProductGroupCollectionFactory
     */
    protected $_productGroupCollectionFactory;

    /**
     * @var WarehouseCollectionFactory
     */
    protected $_warehouseCollectionFactory;

    public function __construct(
        ProductGroupCollectionFactory $productGroupCollectionFactory,
        WarehouseCollectionFactory $warehouseCollectionFactory
    ) {
        $this->_productGroupCollectionFactory = $productGroupCollectionFactory;
        $this->_productGroupCollectionFactory = $warehouseCollectionFactory;
    }

    public function getWarehouses() {
        /** @var $warehouseCollection WarehouseCollection */
        $warehouseCollection = $this->_warehouseCollectionFactory->create();

        return $warehouseCollection->getItems();
    }

    public function getProductGroup()
    {
        /** @var $productGroupCollection ProductGroupCollection */
        $productGroupCollection = $this->_productGroupCollectionFactory->create();

        return $productGroupCollection->getItems();
    }

    public function getCategories()
    {
        /** @var $productGroupCollection ProductGroupCollection */
        $productGroupCollection = $this->_productGroupCollectionFactory->create();
        $itemArray = [];
        $i = 0;
        foreach ($productGroupCollection->getItems() as $item) {
            if ($i!==0) {
                if ($item['category_title'] !== $itemArray[$i-1]['category_title']) {
                    $itemArray[$i]['category_id'] = $item['category_id'];
                    $itemArray[$i]['category_title'] = $item['category_title'];
                    $i++;
                }
            } else {
                $itemArray[$i]['category_id'] = $item['category_id'];
                $itemArray[$i]['category_title'] = $item['category_title'];
                $i++;
            }
        }
        return $itemArray;
    }
    public function getSubCategories()
    {
        /** @var $productGroupCollection ProductGroupCollection */
        $productGroupCollection = $this->_productGroupCollectionFactory->create();
        $itemArray = [];
        $i = 0;
        foreach ($productGroupCollection->getItems() as $item) {
            if ($i!==0) {
                if ($item['subcategory_title'] !== $itemArray[$i-1]['subcategory_title']) {
                    $itemArray[$i]['category_id'] = $item['category_id'];
                    $itemArray[$i]['subcategory_id'] = $item['subcategory_id'];
                    $itemArray[$i]['subcategory_title'] = $item['subcategory_title'];
                    $i++;
                }
            } else {
                $itemArray[$i]['category_id'] = $item['category_id'];
                $itemArray[$i]['subcategory_id'] = $item['subcategory_id'];
                $itemArray[$i]['subcategory_title'] = $item['subcategory_title'];
                $i++;
            }
        }

        return $itemArray;
    }
}
