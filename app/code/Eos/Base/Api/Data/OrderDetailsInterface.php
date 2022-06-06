<?php declare(strict_types=1);

namespace Eos\Base\Api\Data;

/**
 * Order interface.
 * @api
 * @since 1.0.0
 */
interface OrderDetailsInterface
{
    const ID = 'entity_id';
    const ORDER_ID = 'entity_id';
    const CUSTOMER_ID = 'customer_id';
    const PRODUCT_BRAND = 'product_brand';
    const PRODUCT_TAX_NR = 'product_tax_nr';
    const PRODUCT_TITLE = 'product_title';
    const PRODUCT_AMOUNT = 'product_amount';
    const PRODUCT_TYPE = 'product_type';
    const PRODUCT_PRICE_NET = 'product_price_net';
    const PRODUCT_PRICE_GROSS = 'product_price_gross';
    const PRODUCT_TAX = 'product_tax';
    const CREATED_AT = 'created_at';
    const MODIFIED_AT = 'modified_at';

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
     * @return int
     */
    public function getOrderId();

    /**
     * @param int $order_id
     * @return $this
     */
    public function setOrderId($order_id);

    /**
     * @return string
     */
    public function getProductBrand();

    /**
     * @param string $product_brand
     * @return $this
     */
    public function setProductBrand($product_brand);

    /**
     * @return int
     */
    public function getProductTaxNr();

    /**
     * @param int $product_tax_nr
     * @return $this
     */
    public function setProductTaxNr($product_tax_nr);

    /**
     * @return string
     */
    public function getProductTitle();

    /**
     * @param string $product_title
     * @return $this
     */
    public function setProductTitle($product_title);

    /**
     * @return int
     */
    public function getProductAmount();

    /**
     * @param int $product_amount
     * @return $this
     */
    public function setProductAmount($product_amount);

    /**
     * @return string
     */
    public function getProductType();

    /**
     * @param string $product_type
     * @return $this
     */
    public function setProductType($product_type);

    /**
     * @return float
     */
    public function getProductPriceNet();

    /**
     * @param float $product_price_net
     * @return $this
     */
    public function setProductPriceNet($product_price_net);

    /**
     * @return float
     */
    public function getProductPriceGross();

    /**
     * @param float $product_price_gross
     * @return $this
     */
    public function setProductPriceGross($product_price_gross);

    /**
     * @return float
     */
    public function getProductTax();

    /**
     * @param float $product_tax
     * @return $this
     */
    public function setProductTax($product_tax);


    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @return string
     */
    public function getModifiedAt();



}
