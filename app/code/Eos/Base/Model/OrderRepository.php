<?php declare(strict_types=1);

namespace Eos\Base\Model;

use Eos\Base\Api\Data\OrderInterface;
use Eos\base\Model\ResourceModel\Order as OrderResourceModel;
use Eos\Base\Api\OrderRepositoryInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private OrderFactory $orderFactory,
        private OrderResourceModel $orderResourceModel,
    ) {}

    public function getById(int $id): OrderInterface
    {
        $order = $this->orderFactory->create();
        $this->orderResourceModel->load($order, $id);

        if (!$order->getId()) {
            throw new NoSuchEntityException(__('The order with the "%1" ID doesn\'t exist.', $id));
        }
        return $order;
    }

    public function save(OrderInterface $order): OrderInterface
    {
        try {
            $this->orderResourceModel->save($order);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $order;
    }

    public function deleteById(int $id): bool
    {
        $order = $this->getById($id);

        try {
            $this->orderResourceModel->delete($order);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }

        return true;
    }
}
