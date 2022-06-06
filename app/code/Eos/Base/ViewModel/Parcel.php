<?php declare(strict_types=1);

namespace Eos\Base\ViewModel;

use Eos\Base\Api\Data\ParcelInterface;
use Eos\Base\Api\ParcelRepositoryInterface;
use Eos\Base\Model\ResourceModel\Parcel\Collection;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Parcel implements ArgumentInterface
{

    public function __construct(
        private Collection $collection,
        private ParcelRepositoryInterface $parcelRepository,
        private RequestInterface $request,
    ) {}

    public function getList(): array
    {
        return $this->collection->getItems();
    }

    public function getCount(): int
    {
        return $this->collection->count();
    }

    public function getDetail(): ParcelInterface
    {
        $id = (int) $this->request->getParam('id');
        return $this->parcelRepository->getById($id);
    }
}
