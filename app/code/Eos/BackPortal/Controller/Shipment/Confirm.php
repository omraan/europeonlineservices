<?php

namespace Eos\BackPortal\Controller\Shipment;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

use Eos\Base\Model\ResourceModel\Parcel\Collection as ParcelCollection;
use Eos\Base\Model\ResourceModel\Parcel\CollectionFactory as ParcelCollectionFactory;
use Eos\Base\Model\ResourceModel\Shipment\Collection as ShipmentCollection;
use Eos\Base\Model\ResourceModel\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Eos\Base\Model\ShipmentFactory;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterfaceAlias;
use Magento\Framework\View\Result\PageFactory as PageFactoryAlias;
use Magento\Framework\Webapi\Soap\ClientFactory;
use Magento\Framework\App\Request\Http;

// Composition implements an action interface. Common interfaces to implement:
// Create - HttpPutActionInterface
// Read - HttpGetActionInterface
// Update - HttpPostActionInterface
// Delete - HttpDeleteActionInterface
class Confirm implements HttpGetActionInterfaceAlias
{
    /** @var PageFactoryAlias */
    private $pageFactory;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var ShipmentFactory
     */
    protected $_shipment;

    /**
     * @var AddressFactory
     */

    protected $_addressFactory;

    /**
     * @var ShipmentCollectionFactory
     */
    protected $_shipmentCollectionFactory;

    /**
     * @var ParcelCollectionFactory
     */
    protected $_parcelCollectionFactory;

    /**
     * @var ClientFactory
     */
    protected $soapClientFactory;


    /**
     * @var Http
     */

    protected $request;


    // Instantiating the Context object is no longer required
    public function __construct(
        ClientFactory $soapClientFactory,
        Http $request,
        Session $_customerSession,
        ShipmentFactory $shipment,
        ShipmentCollectionFactory $shipmentCollectionFactory,
        ParcelCollectionFactory $parcelCollectionFactory,
        AddressFactory $_addressFactory,
        PageFactoryAlias $pageFactory
    ) {
        // Calling parent::__construct() is also no longer needed
        $this->pageFactory = $pageFactory;
        $this->request = $request;
        $this->_customerSession = $_customerSession;
        $this->_shipment = $shipment;
        $this->_shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->_parcelCollectionFactory = $parcelCollectionFactory;
        $this->_addressFactory = $_addressFactory;
        $this->soapClientFactory = $soapClientFactory;
    }

    public function execute()
    {


         return $this->pageFactory->create();
    }


}
