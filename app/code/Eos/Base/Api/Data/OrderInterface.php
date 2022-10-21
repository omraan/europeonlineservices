<?php declare(strict_types=1);

namespace Eos\Base\Api\Data;

/**
 * Order interface.
 * @api
 * @since 1.0.0
 */
interface OrderInterface
{
    const ID = 'entity_id';
    const SHIPMENT_ID = 'entity_id';
    const CUSTOMER_ID = 'customer_id';
    const WAREHOUSE_ID = 'entity_id';
    const WEBSHOP_TITLE = 'webshop_title';
    const WEBSHOP_CURRENCY = 'webshop_currency';
    const WEBSHOP_ORDER_NR = 'webshop_order_nr';
    const WEBSHOP_ORDER_DATE = 'webshop_order_date';
    const WEBSHOP_TRACKING_NUMBER = 'webshop_tracking_number';
    const STATUS = 'status';
    const BOOK_IND = 'book_ind';
    const WEBSHOP_ORDER_TOTAL_PRICE_NET = 'webshop_order_total_price_net';
    const WEBSHOP_ORDER_TOTAL_PRICE_GROSS = 'webshop_order_total_price_gross';
    const WEBSHOP_ORDER_COSTS_PRICE_NET = 'webshop_order_costs_price_net';
    const WEBSHOP_ORDER_COSTS_PRICE_GROSS = 'webshop_order_costs_price_gross';
    const WEBSHOP_ORDER_DISCOUNT_PRICE_NET = 'webshop_order_discount_price_net';
    const WEBSHOP_ORDER_DISCOUNT_PRICE_GROSS = 'webshop_order_discount_price_gross';
    const WEIGHT = 'weight';
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
    public function getShipmentId();

    /**
     * @param int $shipment_id
     * @return $this
     */
    public function setShipmentId($shipment_id);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $customer_id
     * @return $this
     */
    public function setCustomerId($customer_id);

    /**
     * @return int
     */
    public function getWarehouseId();

    /**
     * @param int $warehouse_id
     * @return $this
     */
    public function setWarehouseId($warehouse_id);

    /**
     * @return string
     */
    public function getWebshopTitle();

    /**
     * @param string $webshop_title
     * @return $this
     */
    public function setWebshopTitle($webshop_title);

    /**
     * @return string
     */
    public function getWebshopCurrency();

    /**
     * @param string $webshop_currency
     * @return $this
     */
    public function setWebshopCurrency($webshop_currency);

    /**
     * @return string
     */
    public function getWebshopOrderNr();

    /**
     * @param string $webshop_order_nr
     * @return $this
     */
    public function setWebshopOrderNr($webshop_order_nr);

    /**
     * @return string
     */
    public function getWebshopOrderDate();

    /**
     * @param string $webshop_order_date
     * @return $this
     */
    public function setWebshopOrderDate($webshop_order_date);

    /**
     * @return string
     */
    public function getWebshopTrackingNumber();

    /**
     * @param string $webshop_tracking_number
     * @return $this
     */
    public function setWebshopTrackingNumber($webshop_tracking_number);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return int
     */
    public function getBookInd();

    /**
     * @param int $bookInd
     * @return $this
     */
    public function setBookInd($bookInd);

    /**
     * @return float
     */
    public function getWebshopOrderTotalPriceNet();

    /**
     * @param float $webshop_order_total_price_net
     * @return $this
     */
    public function setWebshopOrderTotalPriceNet($webshop_order_total_price_net);

    /**
     * @return float
     */
    public function getWebshopOrderTotalPriceGross();

    /**
     * @param float $webshop_order_total_price_gross
     * @return $this
     */
    public function setWebshopOrderTotalPriceGross($webshop_order_total_price_gross);

    /**
     * @return float
     */
    public function getWebshopOrderCostsPriceNet();

    /**
     * @param float $webshop_order_costs_price_net
     * @return $this
     */
    public function setWebshopOrderCostsPriceNet($webshop_order_costs_price_net);

    /**
     * @return float
     */
    public function getWebshopOrderCostsPriceGross();

    /**
     * @param float $webshop_order_costs_price_gross
     * @return $this
     */
    public function setWebshopOrderCostsPriceGross($webshop_order_costs_price_gross);

    /**
     * @return float
     */
    public function getWebshopOrderDiscountPriceNet();

    /**
     * @param float $webshop_order_discount_price_net
     * @return $this
     */
    public function setWebshopOrderDiscountPriceNet($webshop_order_discount_price_net);

    /**
     * @return float
     */
    public function getWebshopOrderDiscountPriceGross();

    /**
     * @param float $webshop_order_discount_price_gross
     * @return $this
     */
    public function setWebshopOrderDiscountPriceGross($webshop_order_discount_price_gross);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @return string
     */
    public function getModifiedAt();



}
