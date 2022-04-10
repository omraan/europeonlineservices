<?php
namespace Eos\Base\Helper;

use Eos\Base\Model\ResourceModel\Order\Collection as OrderCollection;
use Eos\Base\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use \Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AddressFactory;

class CalculatePrice extends AbstractHelper
{
    /** @var OrderCollectionFactory */
    protected $_orderCollectionFactory;

    public function __construct(
        Context         $context,
        OrderCollectionFactory $orderCollectionFactory
    )
    {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context);
    }

    public function calculatePrice($shipment_id, $total = false)
    {
        /** @var $orderCollection OrderCollection */
        $orderCollection = $this->_orderCollectionFactory->create();

        // Loop through all order_{orderID}

        $orderCollection->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns([
                'tracking_number'       => 'main_table.webshop_tracking_number',
                'parcel_id'            =>  'eos_parcel.entity_id',
                'weight'                =>  'eos_parcel.weight',
                'length'                =>  'eos_parcel.length',
                'width'                 =>  'eos_parcel.width',
                'height'                =>  'eos_parcel.height'
            ])
            ->joinLeft(
                "eos_parcel",
                "eos_parcel.tracking_number = main_table.webshop_tracking_number"
            )->where('main_table.shipment_id = ' . $shipment_id);
        $allItems = $orderCollection->getItems();

        $priceList = [];
        $itemCounter = 0;
        $totalPrice = 0;
        $totalWeight = 0;

        foreach ($allItems as $item) {
            $weight = $item->getData('weight');
            $height = $item->getData('height');
            $length = $item->getData('length');
            $width = $item->getData('width');
            $divider = 5000;

            // Placeholder value for Price per Unit or KG
            $pricePerUnit = 2.70;

            $formula = ($height * $length * $width) / $divider;

            $priceList[$itemCounter]['parcel_id'] = $item->getData('parcel_id');

            // Minimum of 2KG
            $priceList[$itemCounter]['weight'] = ($weight >= 2 ? $weight : 2);
            $priceList[$itemCounter]['formula'] = $formula;
            $priceList[$itemCounter]['volumeWeight'] = $weight >= $formula ? $weight : $formula;
            $priceList[$itemCounter]['price'] = 15 +  ( $weight >= $formula ? $weight : $formula ) * $pricePerUnit;
            $totalWeight = $totalWeight + $priceList[$itemCounter]['volumeWeight'];
            $totalPrice = $totalPrice + $priceList[$itemCounter]['price'];

            $itemCounter++;

        }
        $totals['weight'] = $totalWeight;
        $totals['price'] = $totalPrice;

        return $total ? $totals : $priceList;
    }

}
