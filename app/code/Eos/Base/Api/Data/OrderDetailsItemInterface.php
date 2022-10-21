<?php declare(strict_types=1);

namespace Eos\Base\Api\Data;

/**
 * Order interface.
 * @api
 * @since 1.0.0
 */
interface OrderDetailsItemInterface
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
     * @return int
     */
    public function getOrderId();

    /**
     * @return string
     */
    public function getProductBrand();

    /**
     * @return int
     */
    public function getProductTaxNr();

    /**
     * @return string
     */
    public function getProductTitle();

    /**
     * @return int
     */
    public function getProductAmount();


    /**
     * @return string
     */
    public function getProductType();

    /**
     * @return float
     */
    public function getProductPriceNet();

    /**
     * @return float
     */
    public function getProductPriceGross();

    /**
     * @return float
     */
    public function getProductTax();

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @return string
     */
    public function getModifiedAt();



}
