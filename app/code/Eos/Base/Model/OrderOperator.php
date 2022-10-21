<?php

namespace Eos\Base\Model;

use Eos\Base\Api\Data\OrderResultInterface;
use Eos\Base\Api\Data\OrderResultInterfaceFactory;
use Eos\Base\Api\OrderOperatorInterface;
use Eos\Base\Model\ResourceModel\OrderDetails\Collection as OrderDetailsCollection;

class OrderOperator implements OrderOperatorInterface
{

    public function __construct(
        private OrderResultInterfaceFactory $orderResultInterfaceFactory,
        private OrderDetailsCollection $orderDetailsCollection
    ) { }

    /**
     * @return \Eos\Base\Api\Data\OrderResultInterface
     */

    public function parse(int $id) : OrderResultInterface
    {
        $data = $this->orderDetailsCollection->addFieldToFilter('main_table.entity_id', ['eq' => $id])->getItems();

        $array = [];
        $i = 0;
        foreach($data as $item) {
            $array[$i]['entity_id'] = $item['entity_id'];
            $array[$i]['order_id'] = $item['order_id'];
            $array[$i]['product_brand'] = $item['product_brand'];
            $array[$i]['product_tax_nr'] = $item['product_tax_nr'];
            $array[$i]['product_title'] = $item['product_title'];
            $array[$i]['product_amount'] = $item['product_amount'];
            $array[$i]['product_price_net'] = $item['product_price_net'];
            $array[$i]['product_price_gross'] = $item['product_price_gross'];
            $array[$i]['product_type'] = $item['product_type'];
            $array[$i]['product_tax'] = $item['product_tax'];
            $array[$i]['created_at'] = $item['created_at'];
            $array[$i]['modified_at'] = $item['modified_at'];
            $i++;
        }

        return $this->orderResultInterfaceFactory->create([
            'itemsData' => $array
        ]);
    }
}
