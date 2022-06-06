<?php declare(strict_types=1);

namespace Eos\Base\Model;

use Eos\Base\Api\Data\ParcelInterface;
use Eos\base\Model\ResourceModel\Parcel as ParcelResourceModel;
use Eos\Base\Api\ParcelRepositoryInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class ParcelRepository implements ParcelRepositoryInterface
{
    public function __construct(
        private ParcelFactory $parcelFactory,
        private ParcelResourceModel $parcelResourceModel,
    ) {}

    public function getById(int $id): ParcelInterface
    {
        $parcel = $this->parcelFactory->create();
        $this->parcelResourceModel->load($parcel, $id);

        if (!$parcel->getId()) {
            throw new NoSuchEntityException(__('The Parcel with the "%1" ID doesn\'t exist.', $id));
        }

        return $parcel;
    }

    public function save(ParcelInterface $parcel): ParcelInterface
    {
        try {
            $this->parcelResourceModel->save($parcel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $parcel;
    }

    public function deleteById(int $id): bool
    {
        $parcel = $this->getById($id);

        try {
            $this->parcelResourceModel->delete($parcel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }

        return true;
    }
}
