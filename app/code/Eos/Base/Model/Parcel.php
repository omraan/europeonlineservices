<?php declare(strict_types=1);

namespace Eos\Base\Model;

use Eos\Base\Api\Data\ParcelInterface;
use Magento\Framework\Model\AbstractModel;

class Parcel extends AbstractModel implements ParcelInterface
{
    protected function _construct()
    {
        $this->_init(ResourceModel\Parcel::class);
    }

    public function getTrackingNumber()
    {
        return $this->getData(self::TRACKING_NUMBER);
    }

    public function setTrackingNumber($tracking_number)
    {
        return $this->setData(self::TRACKING_NUMBER, $tracking_number);
    }

    public function getWidth()
    {
        return $this->getData(self::WIDTH);
    }

    public function setWidth($width)
    {
        return $this->setData(self::WIDTH, $width);
    }

    public function getHeight()
    {
        return $this->getData(self::HEIGHT);
    }

    public function setHeight($height)
    {
        return $this->setData(self::HEIGHT, $height);
    }

    public function getLength()
    {
        return $this->getData(self::LENGTH);
    }

    public function setLength($length)
    {
        return $this->setData(self::LENGTH, $length);
    }

    public function getWeight()
    {
        return $this->getData(self::WEIGHT);
    }

    public function setWeight($weight)
    {
        return $this->setData(self::WEIGHT, $weight);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }
}
