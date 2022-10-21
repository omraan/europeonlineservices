<?php  declare(strict_types=1);

namespace Eos\Base\Model;

use Eos\Base\Api\Data\OrderDetailsItemInterface;
use Magento\Tests\NamingConvention\true\string;

class OrderDetailsItemItem implements OrderDetailsItemInterface
{

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $orderId;

    /**
     * @var string
     */
    private $productBrand;

    /**
     * @var int
     */
    private $productTaxNr;

    /**
     * @var string
     */
    private $productTitle;

    /**
     * @var int
     */
    private $productAmount;

    /**
     * @var float
     */
    private $productPriceNet;

    /**
     * @var float
     */
    private $productPriceGross;

    /**
     * @var float
     */
    private $productType;

    /**
     * @var float
     */
    private $productTax;

    /**
     * @var string
     */
    private $createdAt;

    /**
     * @var string
     */
    private $modifiedAt;

    /**
     * @inheritdoc
     */
    public function __construct(
        int $id,
        int $orderId,
        string $productBrand,
        int $productTaxNr,
        string $productTitle,
        int $productAmount,
        float $productPriceNet,
        float $productPriceGross,
        string $productType,
        float $productTax,
        string $createdAt,
        string $modifiedAt
    ) {
        $this->id = $id;
        $this->orderId = $orderId;
        $this->productBrand = $productBrand;
        $this->productTaxNr = $productTaxNr;
        $this->productTitle = $productTitle;
        $this->productAmount = $productAmount;
        $this->productPriceNet = $productPriceNet;
        $this->productPriceGross = $productPriceGross;
        $this->productType = $productType;
        $this->productTax = $productTax;
        $this->createdAt = $createdAt;
        $this->modifiedAt = $modifiedAt;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @inheritdoc
     */
    public function getProductBrand()
    {
        return $this->productBrand;
    }

    /**
     * @inheritdoc
     */
    public function getProductTaxNr()
    {
        return $this->productTaxNr;
    }

    /**
     * @inheritdoc
     */
    public function getProductTitle()
    {
        return $this->productTitle;
    }

    /**
     * @inheritdoc
     */
    public function getProductAmount()
    {
        return $this->productAmount;
    }

    /**
     * @inheritdoc
     */
    public function getProductType()
    {
        return $this->productType;
    }

    /**
     * @inheritdoc
     */
    public function getProductPriceNet()
    {
        return $this->productPriceNet;
    }

    /**
     * @inheritdoc
     */
    public function getProductPriceGross()
    {
        return $this->productPriceGross;
    }

    /**
     * @inheritdoc
     */
    public function getProductTax()
    {
        return $this->productTax;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @inheritdoc
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }


}
