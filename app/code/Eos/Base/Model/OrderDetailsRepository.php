<?php declare(strict_types=1);

namespace Eos\Base\Model;

use Eos\Base\Api\Data\OrderDetailsInterface;
use Eos\base\Model\ResourceModel\OrderDetails as OrderDetailsResourceModel;
use Eos\Base\Api\OrderDetailsRepositoryInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class OrderDetailsRepository implements OrderDetailsRepositoryInterface
{
    public function __construct(
        private OrderDetailsFactory $orderDetailsFactory,
        private OrderDetailsResourceModel $orderDetailsResourceModel,
    ) {}

    public function getById(int $id): OrderDetailsInterface
    {
        $orderDetails = $this->orderDetailsFactory->create();
        $this->orderDetailsResourceModel->load($orderDetails, $id);

        if (!$orderDetails->getId()) {
            throw new NoSuchEntityException(__('The order with the "%1" ID doesn\'t exist.', $id));
        }
        return $orderDetails;
    }

    public function save(OrderDetailsInterface $orderDetails): OrderDetailsInterface
    {
        try {
            $this->orderDetailsResourceModel->save($orderDetails);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $orderDetails;
    }

    public function deleteById(int $id): bool
    {
        $orderDetails = $this->getById($id);

        try {
            $this->orderDetailsResourceModel->delete($orderDetails);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }

        return true;
    }
}
