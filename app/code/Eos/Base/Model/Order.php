<?php declare(strict_types=1);

namespace Eos\Base\Model;

use Eos\Base\Api\Data\OrderInterface;
use Magento\Framework\Model\AbstractModel;

class Order extends AbstractModel implements OrderInterface
{

    protected function _construct()
    {
        $this->_init(ResourceModel\Order::class);
    }

    public function getShipmentId()
    {
        return $this->getData(self::SHIPMENT_ID);
    }

    public function setShipmentId($shipment_id)
    {
        return $this->setData(self::SHIPMENT_ID, $shipment_id);
    }

    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    public function setCustomerId($customer_id)
    {
        return $this->setData(self::CUSTOMER_ID, $customer_id);
    }

    public function getWarehouseId()
    {
        return $this->getData(self::WAREHOUSE_ID);
    }

    public function setWarehouseId($warehouse_id)
    {
        return $this->setData(self::WAREHOUSE_ID, $warehouse_id);
    }

    public function getWebshopTitle()
    {
        return $this->getData(self::WEBSHOP_TITLE);
    }

    public function setWebshopTitle($webshop_title)
    {
        return $this->setData(self::WEBSHOP_TITLE, $webshop_title);
    }

    public function getWebshopCurrency()
    {
        return $this->getData(self::WEBSHOP_CURRENCY);
    }

    public function setWebshopCurrency($webshop_currency)
    {
        return $this->setData(self::WEBSHOP_CURRENCY, $webshop_currency);
    }

    public function getWebshopOrderNr()
    {
        return $this->getData(self::WEBSHOP_ORDER_NR);
    }

    public function setWebshopOrderNr($webshop_order_nr)
    {
        return $this->setData(self::WEBSHOP_ORDER_NR, $webshop_order_nr);
    }

    public function getWebshopOrderDate()
    {
        return $this->getData(self::WEBSHOP_ORDER_DATE);
    }

    public function setWebshopOrderDate($webshop_order_date)
    {
        return $this->setData(self::WEBSHOP_ORDER_DATE, $webshop_order_date);
    }

    public function getWebshopTrackingNumber()
    {
        return $this->getData(self::WEBSHOP_TRACKING_NUMBER);
    }

    public function setWebshopTrackingNumber($webshop_tracking_number)
    {
        return $this->setData(self::WEBSHOP_TRACKING_NUMBER, $webshop_tracking_number);
    }

    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    public function getBookInd()
    {
        return $this->getData(self::BOOK_IND);
    }

    public function setBookInd($bookInd)
    {
        return $this->setData(self::BOOK_IND, $bookInd);
    }

    public function getWebshopOrderTotalPriceNet()
    {
        return $this->getData(self::WEBSHOP_ORDER_TOTAL_PRICE_NET);
    }

    public function setWebshopOrderTotalPriceNet($webshop_order_total_price_net)
    {
        return $this->setData(self::WEBSHOP_ORDER_TOTAL_PRICE_NET, $webshop_order_total_price_net);
    }

    public function getWebshopOrderTotalPriceGross()
    {
        return $this->getData(self::WEBSHOP_ORDER_TOTAL_PRICE_GROSS);
    }

    public function setWebshopOrderTotalPriceGross($webshop_order_total_price_gross)
    {
        return $this->setData(self::WEBSHOP_ORDER_TOTAL_PRICE_GROSS, $webshop_order_total_price_gross);
    }

    public function getWebshopOrderCostsPriceNet()
    {
        return $this->getData(self::WEBSHOP_ORDER_COSTS_PRICE_NET);
    }

    public function setWebshopOrderCostsPriceNet($webshop_order_costs_price_net)
    {
        return $this->setData(self::WEBSHOP_ORDER_COSTS_PRICE_NET, $webshop_order_costs_price_net);
    }

    public function getWebshopOrderCostsPriceGross()
    {
        return $this->getData(self::WEBSHOP_ORDER_COSTS_PRICE_GROSS);
    }

    public function setWebshopOrderCostsPriceGross($webshop_order_costs_price_gross)
    {
        return $this->setData(self::WEBSHOP_ORDER_COSTS_PRICE_GROSS, $webshop_order_costs_price_gross);
    }

    public function getWebshopOrderDiscountPriceNet()
    {
        return $this->getData(self::WEBSHOP_ORDER_DISCOUNT_PRICE_NET);
    }

    public function setWebshopOrderDiscountPriceNet($webshop_order_discount_price_net)
    {
        return $this->setData(self::WEBSHOP_ORDER_DISCOUNT_PRICE_NET, $webshop_order_discount_price_net);
    }

    public function getWebshopOrderDiscountPriceGross()
    {
        return $this->getData(self::WEBSHOP_ORDER_DISCOUNT_PRICE_GROSS);
    }

    public function setWebshopOrderDiscountPriceGross($webshop_order_discount_price_gross)
    {
        return $this->setData(self::WEBSHOP_ORDER_DISCOUNT_PRICE_GROSS, $webshop_order_discount_price_gross);
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
