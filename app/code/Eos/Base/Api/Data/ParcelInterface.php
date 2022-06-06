<?php declare(strict_types=1);

namespace Eos\Base\Api\Data;

/**
 * Parcel interface.
 * @api
 * @since 1.0.0
 */
interface ParcelInterface
{
    const ID = 'entity_id';
    const TRACKING_NUMBER = 'tracking_number';
    const WIDTH = 'width';
    const HEIGHT = 'height';
    const LENGTH = 'length';
    const WEIGHT = 'weight';
    const CREATED_AT = 'created_at';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getTrackingNumber();

    /**
     * @param string $tracking_number
     * @return $this
     */
    public function setTrackingNumber($tracking_number);

    /**
     * @return float
     */
    public function getWidth();

    /**
     * @param float $width
     * @return $this
     */
    public function setWidth($width);

    /**
     * @return float
     */
    public function getHeight();

    /**
     * @param float $height
     * @return $this
     */
    public function setHeight($height);

    /**
     * @return float
     */
    public function getLength();

    /**
     * @param float $length
     * @return $this
     */
    public function setLength($length);

    /**
     * @return float
     */
    public function getWeight();

    /**
     * @param float $weight
     * @return $this
     */
    public function setWeight($weight);

    /**
     * @return string
     */
    public function getCreatedAt();
}
