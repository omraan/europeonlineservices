<?php declare(strict_types=1);

namespace Eos\Base\Api;

use Eos\Base\Api\Data\OrderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Order CRUD interface.
 * @api
 * @since 1.0.0
 */
interface OrderRepositoryInterface
{
    /**
     * @param int $id
     * @return OrderInterface
     * @throws LocalizedException
     */
    public function getById(int $id): OrderInterface;

    /**
     * @param OrderInterface $order
     * @return OrderInterface
     * @throws LocalizedException
     */
    public function save(OrderInterface $order): OrderInterface;

    /**
     * @param int $id
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $id): bool;
}

