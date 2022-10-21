<?php

namespace Eos\Base\Model;

use Eos\Base\Api\Data\OrderResultInterface;

class OrderResult implements OrderResultInterface
{
    /**
     * @var \Eos\Base\Api\Data\OrderDetailsItemInterface[]
     */
    private $items = [];


    /**
     * OperationResult constructor.
     * @param array $itemsData
     * @param \Eos\Base\Api\Data\OrderDetailsInterfaceFactory $orderDetailsInterfaceFactory
     */
    public function __construct(
        $itemsData,
        \Eos\Base\Api\Data\OrderDetailsInterfaceFactory $orderDetailsInterfaceFactory
    ) {
        foreach ($itemsData as $item) {
            $this->items[] = $orderDetailsInterfaceFactory->create([
                'id' => $item['entity_id'],
                'order_id' => $item['order_id'],
                'product_brand' => $item['product_brand'],
                'product_tax_nr' => $item['product_tax_nr'],
                'product_title' => $item['product_title'],
                'product_amount' => $item['product_amount'],
                'product_price_net' => $item['product_price_net'],
                'product_price_gross' => $item['product_price_gross'],
                'product_type' => $item['product_type'],
                'product_tax' => $item['product_tax'],
                'created_at' => $item['created_at'],
                'modified_at' => $item['modified_at']
            ]);
        }
    }

    /**
     * @return \Eos\Base\Api\Data\OrderDetailsItemInterface[]
     */
    public function getLocalization()
    {

        return $this->items;
    }
}
