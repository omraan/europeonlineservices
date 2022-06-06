<?php declare(strict_types=1);

namespace Eos\Base\Api;

use Eos\Base\Api\Data\ParcelInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Parcel CRUD interface.
 * @api
 * @since 1.0.0
 */
interface ParcelRepositoryInterface
{
    /**
     * @param int $id
     * @return ParcelInterface
     * @throws LocalizedException
     */
    public function getById(int $id): ParcelInterface;

    /**
     * @param ParcelInterface $parcel
     * @return ParcelInterface
     * @throws LocalizedException
     */
    public function save(ParcelInterface $parcel): ParcelInterface;

    /**
     * @param int $id
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $id): bool;
}

