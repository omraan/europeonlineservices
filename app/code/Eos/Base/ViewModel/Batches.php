<?php
namespace Eos\Base\ViewModel;

use Eos\Base\Model\ResourceModel\Batch\Collection as BatchCollection;
use Eos\Base\Model\ResourceModel\Batch\CollectionFactory as BatchCollectionFactory;
use Eos\Base\Model\ResourceModel\Batch\CollectionWithPallets as BatchPalletCollection;
use Eos\Base\Model\ResourceModel\Batch\CollectionWithPalletsFactory as BatchPalletCollectionFactory;
use Eos\Base\Model\ResourceModel\Shipment\Collection as ShipmentCollection;
use Eos\Base\Model\ResourceModel\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Eos\Base\Model\ResourceModel\Order\Collection as OrderCollection;
use Eos\Base\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Eos\Base\Model\ResourceModel\OrderDetails\Collection as OrderDetailsCollection;
use Eos\Base\Model\ResourceModel\OrderDetails\CollectionFactory as OrderDetailsCollectionFactory;
use Eos\Base\Model\ResourceModel\Country\Collection as CountryCollection;
use Eos\Base\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Eos\Base\Model\ResourceModel\Hs\Collection as ProductGroupCollection;
use Eos\Base\Model\ResourceModel\Hs\CollectionFactory as ProductGroupCollectionFactory;
use Eos\Base\Model\ResourceModel\Warehouse\Collection as WarehouseCollection;
use Eos\Base\Model\ResourceModel\Warehouse\CollectionFactory as WarehouseCollectionFactory;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class Batches implements ArgumentInterface
{
    /**
     * @var BatchCollectionFactory
     */
    protected $batchCollectionFactory;

    /**
     * @var BatchPalletCollectionFactory
     */
    protected $batchPalletCollectionFactory;


    /**
     * @var ShipmentCollectionFactory
     */
    protected $shipmentCollectionFactory;

    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var OrderDetailsCollectionFactory
     */
    protected $orderDetailsCollectionFactory;

    /**
     * @var ProductGroupCollectionFactory
     */
    protected $productGroupCollectionFactory;

    /**
     * @var CountryCollectionFactory
     */
    protected $countryCollectionFactory;

    /**
     * @var WarehouseCollectionFactory
     */
    protected $warehouseCollectionFactory;

    public function __construct(
        BatchCollectionFactory $batchCollectionFactory,
        BatchPalletCollectionFactory $batchPalletCollectionFactory,
        ShipmentCollectionFactory $shipmentCollectionFactory,
        OrderCollectionFactory $orderCollectionFactory,
        OrderDetailsCollectionFactory $orderDetailsCollectionFactory,
        ProductGroupCollectionFactory $productGroupCollectionFactory,
        CountryCollectionFactory $countryCollectionFactory,
        WarehouseCollectionFactory $warehouseCollectionFactory
    ) {
        $this->batchCollectionFactory = $batchCollectionFactory;
        $this->batchPalletCollectionFactory = $batchPalletCollectionFactory;
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderDetailsCollectionFactory = $orderDetailsCollectionFactory;
        $this->productGroupCollectionFactory = $productGroupCollectionFactory;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->warehouseCollectionFactory = $warehouseCollectionFactory;
    }

    public function getBatches($batchId = false)
    {
        /** @var $batchCollection BatchCollection */
        $batchCollection = $this->batchCollectionFactory->create();

        if ($batchId) {
            $batchCollection->addFieldToFilter('entity_id', ['eq' => $batchId]);
            return $batchCollection->getFirstItem();
        } else {
            return $batchCollection->getItems();
        }


    }

    public function getBatchPallets($batchId = false)
    {
        /** @var $batchPalletCollection BatchPalletCollection */
        $batchPalletCollection = $this->batchPalletCollectionFactory->create();

        if ($batchId) {
            $batchPalletCollection->addFieldToFilter('batch_id', ['eq' => $batchId]);
        }

        return $batchPalletCollection->getItems();
    }

    public function getPalletAmount($batchId)
    {
        /** @var $batchPalletCollection BatchPalletCollection */
        $batchPalletCollection = $this->batchPalletCollectionFactory->create();
        $batchPalletCollection->addFieldToFilter('batch_id', ['eq' => $batchId]);

        return $batchPalletCollection->getSize();
    }

    public function getBatchesAmount($status = null)
    {

        /** @var $batchCollection BatchCollection */
        $batchCollection = $this->batchCollectionFactory->create();

        if ($status) {
            $statusImplode = implode(",", $status);
            $batchCollection->addFieldToFilter('status', ['in' => $statusImplode]);


        }
        return $batchCollection->getSize();
    }
    public function getOrdersAmount($status = null)
    {

        /** @var $orderCollection OrderCollection */
        $orderCollection = $this->orderCollectionFactory->create();

        if ($status) {
            $statusImplode = implode(",", $status);
            $orderCollection->addFieldToFilter('status', ['in' => $statusImplode]);


        }
        return $orderCollection->getSize();
    }


    public function getParcelAmount($batchId, $awbCode = false)
    {
        /** @var $batchPalletCollection BatchPalletCollection */
        $batchPalletCollection = $this->batchPalletCollectionFactory->create();

        $batchPalletCollection->getSelect()->join(
            ['eos_shipment' => $batchPalletCollection->getTable('eos_shipment')],
            'eos_batch_pallet.awb_code = eos_shipment.awb_code'
        );

        $batchPalletCollection->getSelect()->join(
            ['eos_order' => $batchPalletCollection->getTable('eos_order')],
            'eos_shipment.entity_id = eos_order.shipment_id'
        );

        $batchPalletCollection->getSelect()->join(
            ['eos_parcel' => $batchPalletCollection->getTable('eos_parcel')],
            'eos_order.webshop_tracking_number = eos_parcel.tracking_number'
        );


        $batchPalletCollection->addFieldToFilter('batch_id', ['eq' => $batchId]);

        if ($awbCode) {
            $batchPalletCollection->addFieldToFilter('eos_batch_pallet.awb_code', ['eq' => $awbCode]);
        }

        return $batchPalletCollection->getSize();
    }



    public function getShipments($status = null, $shipment = false)
    {
        /** @var $shipmentCollection ShipmentCollection */
        $shipmentCollection = $this->shipmentCollectionFactory->create();

        if ($status) {
            $shipmentCollection->addFieldToFilter('status', ['like' => "%" . $status . "%"]);

        }
        if ($shipment) {
            $shipmentCollection->addFieldToFilter('entity_id', ['eq' => $shipment]);
        }

        return $shipmentCollection->getItems();
    }

    public function getOrders($status = null, $shipment = false)
    {
        /** @var $orderCollection OrderCollection */
        $orderCollection = $this->orderCollectionFactory->create();

        if ($status) {
            foreach ($status as $key => $value) {
                if ($key != "form_key" && $value != "") {
                    $orderCollection->addFieldToFilter('status', ['like' => "%" . $value . "%"]);
                }
            }
        }
        if ($shipment) {
            $orderCollection->addFieldToFilter('shipment_id', ['neq' => 0]);
        }

        return $orderCollection->getItems();
    }

    public function getOrderDetails($orderId)
    {
        /** @var $orderCollection OrderCollection */
        $orderCollection = $this->orderCollectionFactory->create();

        $orderCollection->addFieldToFilter('order_id', ['eq' => $orderId]);

        return $orderCollection->getItems();
    }

    public function getOrderDetailsWithTaxes($orderId)
    {
        /** @var $orderDetailsCollection OrderDetailsCollection */
        $orderDetailsCollection = $this->orderDetailsCollectionFactory->create()->addFieldToFilter('order_id', ['eq' => $orderId]);

        $orderDetailsCollection->getSelect()->join(
            ['eos_hs_china' => $orderDetailsCollection->getTable('eos_hs_china')],
            'main_table.product_tax_nr = eos_hs_china.product_tax_nr'
        );

        return $orderDetailsCollection->getItems();
    }

    public function countParcelsShipment($shipment)
    {
        /** @var $orderCollection OrderCollection */
        $orderCollection = $this->orderCollectionFactory->create();

        if ($shipment) {
            $orderCollection->addFieldToFilter('shipment_id', ['eq' => $shipment]);
        }
        $orderCollection->getSelect()->join(
            ['eos_parcel' => $orderCollection->getTable('eos_parcel')],
            'main_table.webshop_tracking_number = eos_parcel.tracking_number'
        );

        $count = $orderCollection->getSize();

        return $count;
    }

    public function getProductGroup()
    {
        /** @var $productGroupCollection ProductGroupCollection */
        $productGroupCollection = $this->productGroupCollectionFactory->create();

        return $productGroupCollection->getItems();
    }

    public function getCategories()
    {
        /** @var $productGroupCollection ProductGroupCollection */
        $productGroupCollection = $this->productGroupCollectionFactory->create();
        $itemArray = [];
        $i = 0;
        foreach ($productGroupCollection->getItems() as $item) {
            if ($i !== 0) {
                if ($item['category_title'] !== $itemArray[$i - 1]['category_title']) {
                    $itemArray[$i]['category_id'] = $item['category_id'];
                    $itemArray[$i]['category_title'] = $item['category_title'];
                    $i++;
                }
            } else {
                $itemArray[$i]['category_id'] = $item['category_id'];
                $itemArray[$i]['category_title'] = $item['category_title'];
                $i++;
            }
        }
        return $itemArray;
    }
    public function getSubCategories()
    {
        /** @var $productGroupCollection ProductGroupCollection */
        $productGroupCollection = $this->productGroupCollectionFactory->create();
        $itemArray = [];
        $i = 0;
        foreach ($productGroupCollection->getItems() as $item) {
            if ($i !== 0) {
                if ($item['subcategory_title'] !== $itemArray[$i - 1]['subcategory_title']) {
                    $itemArray[$i]['category_id'] = $item['category_id'];
                    $itemArray[$i]['subcategory_id'] = $item['subcategory_id'];
                    $itemArray[$i]['subcategory_title'] = $item['subcategory_title'];
                    $i++;
                }
            } else {
                $itemArray[$i]['category_id'] = $item['category_id'];
                $itemArray[$i]['subcategory_id'] = $item['subcategory_id'];
                $itemArray[$i]['subcategory_title'] = $item['subcategory_title'];
                $i++;
            }
        }

        return $itemArray;
    }

    public function getCountries($type, $lang = 'en')
    {
        /** @var $countryCollection CountryCollection */
        $countryCollection = $this->countryCollectionFactory->create();
        $countryCollection->addFieldToFilter('country_type', ['like' => $type]);
        $countryCollection->addFieldToFilter('country_lang', ['like' => $lang]);
        return $countryCollection->getItems();
    }

    public function getWarehouses($id = null)
    {
        /** @var $warehouseCollection WarehouseCollection */
        $warehouseCollection = $this->warehouseCollectionFactory->create();

        if ($id) {
            $warehouseCollection->addFieldToFilter('entity_id', ['eq' => $id]);

            return $warehouseCollection->getFirstItem();
        } else {
            return $warehouseCollection->getItems();
        }


    }

    public function getEmptyShipmentMessage()
    {
        return __('You have placed no shipments.');
    }
    public function getEmptyOrdersMessage()
    {
        return __('You have placed no Orders.');
    }
}