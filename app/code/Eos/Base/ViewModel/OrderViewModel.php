<?php

namespace Eos\Base\ViewModel;

use Eos\Base\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

class OrderViewModel extends BaseViewModel
{
    use CollectionMethodsTrait;

    private $orderCollectionFactory;

    protected $orderCollection;

    public function __construct(
        OrderCollectionFactory $orderCollectionFactory
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    public function getOrders()
    {
        if (!$this->orderCollection) {
            $this->orderCollection = $this->orderCollectionFactory->create();
        }
        $this->setCollection($this->orderCollection);
        return $this;
    }

}