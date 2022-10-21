<?php declare(strict_types=1);

namespace Eos\Base\Api;

use Eos\Base\Api\Data\OrderDetailsInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * OrderDetails CRUD interface.
 * @api
 * @since 1.0.0
 */
interface OrderDetailsRepositoryInterface
{
    /**
     * @param int $id
     * @return OrderDetailsInterface
     * @throws LocalizedException
     */
    public function getById(int $id): OrderDetailsInterface;

    /**
     * @param OrderDetailsInterface $orderDetails
     * @return OrderDetailsInterface
     * @throws LocalizedException
     */
    public function save(OrderDetailsInterface $orderDetails): OrderDetailsInterface;

    /**
     * @param int $id
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $id): bool;
}

