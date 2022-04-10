<?php
namespace Eos\Base\ViewModel;

use Eos\Base\Model\ResourceModel\Order\Collection as EntityCollection;
use Eos\Base\Model\ResourceModel\Order\CollectionFactory as EntityCollectionFactory;
use Eos\Base\Model\ResourceModel\OrderDetails\Collection as OrderDetailsCollection;
use Eos\Base\Model\ResourceModel\OrderDetails\CollectionFactory as OrderDetailsCollectionFactory;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class GetOrders implements ArgumentInterface
{

    /**
     * @var EntityCollectionFactory
     */
    protected $entityCollectionFactory;

    /**
     * @var OrderDetailsCollectionFactory
     */
    protected $orderDetailsCollectionFactory;

    /**
     * @var SessionFactory
     */
    protected $customerSession;

    public function __construct(
        EntityCollectionFactory $entityCollectionFactory,
        OrderDetailsCollection $orderDetailsCollectionFactory
    ) {
        $this->entityCollectionFactory = $entityCollectionFactory;
        $this->orderDetailsCollectionFactory = $orderDetailsCollectionFactory;
    }

    public function getOrders($status = null, $shipment = false)
    {
        /** @var $entityCollection EntityCollection */
        $entityCollection = $this->entityCollectionFactory->create();

        if ($status) {
            foreach ($status as $key => $value) {
                if ($key != "form_key" && $value != "") {
                    $entityCollection->addFieldToFilter($key, ['like' => "%" . $value . "%"]);
                }
            }
        }
        if ($shipment) {
            $entityCollection->addFieldToFilter('shipment_id', ['neq' => 0]);
        }

        return $entityCollection->getItems();
    }


    public function getOrder($order_id)
    {

       // /** @var $entityCollection EntityCollection */
        //$entityCollection = $this->entityCollectionFactory->create()->load($order_id);

        return $this->entityCollectionFactory->create()->getItemById($order_id);
    }

    public function getOrderDetails($order_id)
    {

        /** @var $orderDetailsCollection OrderDetailsCollection */
        $orderDetailsCollection = $this->orderDetailsCollectionFactory->create()->load($order_id);

        return $orderDetailsCollection->getItemById($order_id);
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
