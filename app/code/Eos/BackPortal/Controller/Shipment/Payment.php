<?php

namespace Eos\BackPortal\Controller\Shipment;

use Eos\Base\Model\OrderFactory;
use Eos\Base\Model\ResourceModel\Order;
use Eos\Base\Model\ResourceModel\Order\Collection as OrderCollection;
use Eos\Base\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Eos\Base\Model\ShipmentFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;

class Payment extends \Magento\Framework\App\Action\Action
{

    /**
     * @var SessionFactory
     */
    protected $_customerSession;

    /**
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * @var StoreManagerInterface
     */

    protected $_storeManager;

    /**
     * @var OrderFactory
     */
    protected $_order;

    /**
     * @var ShipmentFactory
     */
    protected $_shipment;

    /**
     * @var FormKey
     */

    protected $_formKey;

    /**
     * @var Cart
     */

    protected $_cart;

    /**
     * @var ProductFactory
     */

    protected $_productFactory;

    /**
     * @var Product
     */

    protected $_product;

    /**
     * @var OrderCollectionFactory
     */

    protected $_orderCollectionFactory;

    public function __construct(
        Context $context,
        SessionFactory $customerSession,
        StoreManagerInterface $storeManager,
        DateTime $dateTime,
        OrderFactory $order,
        ShipmentFactory $shipment,
        FormKey $formKey,
        Cart $cart,
        ProductFactory $productFactory,
        Product $product,
        OrderCollectionFactory $orderCollectionFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_dateTime = $dateTime;
        $this->_storeManager = $storeManager;
        $this->_order = $order;
        $this->_shipment = $shipment;
        $this->_cart = $cart;
        $this->_productFactory = $productFactory;
        $this->_formKey = $formKey;
        $this->_product = $product;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context);
    }
    public function execute()
    {
        $post = $this->getRequest()->getParams();

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
            )->where('main_table.shipment_id = ' . $post['shipment_id']);
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

        $shipmentModel = $this->_shipment->create()->load($post['shipment_id']);
        $shipmentModel->setData('status', 'During payment');
        $shipmentModel->setData('total_weight', $totalWeight);
        $shipmentModel->save();

        $productID = 1;
        $this->addProductWithCustomPrice($productID, $totalPrice);

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('checkout');
        return $resultRedirect;
    }

    public function addProductWithCustomPrice($productId, $totalPrice)
    {
        $cart = $this->_cart;
        $params = [];
        $params['qty'] = 1;
        $params['product'] = $productId;

        $product = $this->_product->load($productId);
        $product->setData('custom_overwrite_price', $totalPrice);
        $cart->truncate();
        $cart->addProduct($product, $params);

        $cart->save();
    }
}
