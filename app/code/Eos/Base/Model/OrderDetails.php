<?php declare(strict_types=1);

namespace Eos\Base\Model;

use Eos\Base\Api\Data\OrderDetailsInterface;
use Magento\Framework\Model\AbstractModel;

class OrderDetails extends AbstractModel implements OrderDetailsInterface
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\OrderDetails::class);
    }

    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    public function setOrderId($order_id)
    {
        return $this->setData(self::ORDER_ID, $order_id);
    }

    public function getProductBrand()
    {
        return $this->getData(self::PRODUCT_BRAND);
    }

    public function setProductBrand($product_brand)
    {
        return $this->setData(self::PRODUCT_BRAND, $product_brand);
    }

    public function getProductTaxNr()
    {
        return $this->getData(self::PRODUCT_TAX_NR);
    }

    public function setProductTaxNr($product_tax_nr)
    {
        return $this->setData(self::PRODUCT_TAX_NR, $product_tax_nr);
    }

    public function getProductTitle()
    {
        return $this->getData(self::PRODUCT_TITLE);
    }

    public function setProductTitle($product_title)
    {
        return $this->setData(self::PRODUCT_TITLE, $product_title);
    }

    public function getProductAmount()
    {
        return $this->getData(self::PRODUCT_AMOUNT);
    }

    public function setProductAmount($product_amount)
    {
        return $this->setData(self::PRODUCT_AMOUNT, $product_amount);
    }

    public function getProductType()
    {
        return $this->getData(self::PRODUCT_TYPE);
    }

    public function setProductType($product_type)
    {
        return $this->setData(self::PRODUCT_TYPE, $product_type);
    }

    public function getProductPriceNet()
    {
        return $this->getData(self::PRODUCT_PRICE_NET);
    }

    public function setProductPriceNet($product_price_net)
    {
        return $this->setData(self::PRODUCT_PRICE_NET, $product_price_net);
    }

    public function getProductPriceGross()
    {
        return $this->getData(self::PRODUCT_PRICE_GROSS);
    }

    public function setProductPriceGross($product_price_gross)
    {
        return $this->setData(self::PRODUCT_PRICE_GROSS, $product_price_gross);
    }

    public function getProductTax()
    {
        return $this->getData(self::PRODUCT_TAX);
    }

    public function setProductTax($product_tax)
    {
        return $this->setData(self::PRODUCT_TAX, $product_tax);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    public function getModifiedAt()
    {
        return $this->getData(self::MODIFIED_AT);
    }
}
