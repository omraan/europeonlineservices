<?php

namespace Eos\WmsPortal\Controller\Batches;

use Eos\Base\Model\ResourceModel\Batch\CollectionWithPallets as BatchPalletCollection;
use Eos\Base\Model\ResourceModel\Batch\CollectionWithPalletsFactory as BatchPalletCollectionFactory;
use Eos\Base\Model\ResourceModel\ChineseAddress\CollectionFactory as ChineseAddressCollectionFactory;
use Eos\Base\Model\ResourceModel\Shipment\Collection as ShipmentCollection;
use Eos\Base\Model\ResourceModel\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\App\Filesystem\DirectoryList;


class ExportData extends \Magento\Framework\App\Action\Action
{
    protected $uploaderFactory;

    protected $_locationFactory;

    /**
     * @var BatchPalletCollectionFactory
     */
    protected $_batchPalletCollectionFactory;

    /**
     * @var ShipmentCollectionFactory
     */
    protected $_shipmentCollectionFactory;

    /**
     * @var ChineseAddressCollectionFactory
     */
    protected $_chineseAddressCollectionFactory;

    /**
     * @var CustomerRepository
     */
    protected $_customerRepository;

    /**
     * @var AddressFactory
     */

    protected $_addressFactory;

    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        BatchPalletCollectionFactory $batchPalletCollectionFactory,
        ShipmentCollectionFactory $shipmentCollectionFactory,
        ChineseAddressCollectionFactory $chineseAddressCollectionFactory,
        CustomerRepository $customerRepository,
        AddressFactory $_addressFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Eos\Base\Model\OrderFactory $orderFactory, // This is returns Collaction of Data,
        \Magento\Framework\Escaper $escaper

    ) {
        parent::__construct($context);
        $this->_batchPalletCollectionFactory = $batchPalletCollectionFactory;
        $this->_shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->_chineseAddressCollectionFactory = $chineseAddressCollectionFactory;
        $this->_customerRepository = $customerRepository;
        $this->_addressFactory = $_addressFactory;
        $this->_fileFactory = $fileFactory;
        $this->_locationFactory = $orderFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR); // VAR Directory Path
        $this->_escaper=$escaper;
        parent::__construct($context);
    }

    public function execute()
    {
        $name = date('m-d-Y-H-i-s');
        $filepath = 'export/export-data-' . $name . '.csv'; // at Directory path Create a Folder Export and FIle
        $this->directory->create('export');

        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        /** @var $batchPalletCollection BatchPalletCollection */
        $batchPalletCollection = $this->_batchPalletCollectionFactory->create();
        $batchPalletCollection->addFieldToFilter('status', ['eq' => 'Ready to dispatch']);

        $batches = $batchPalletCollection->getItems();
        //column name dispay in your CSV

        $columns = [
        'MawbNr',
        'MawbIssueDate',
        'MawbCtryDispatch',
        'MasterCtnID',
        'ParcelID',
        'ParcelPacking',
        'OrderNr',
        'OrderHawbNr',

        'OrderSellerName',
        'OrderSellerStreet1',
        'OrderSellerStreet2',
        'OrderSellerZipCode',
        'OrderSellerCity',
        'OrderSellerCountry',
        'OrderSellerEmail',
        'OrderSellerPhone',
        'OrderSellerVATnr',
        'OrderSellerEORInr',

        'OrderBuyerName',
        'OrderBuyerStreet1',
        'OrderBuyerStreet2',
        'OrderBuyerZipCode',
        'OrderBuyerCity',
        'OrderBuyerCountry',

        'InvoiceID',
        'InvoiceNr',
        'InvoiceDate',
        'InvoiceURL',
        'InvoiceTerms',
        'InvoiceCurrency',
        'InvoiceNetAmount',
        'InvoiceVATAmount',
        'InvoiceGrossAmount',
        'InvoiceLineNetAmt',
        'InvoiceLineVATAmt',
        'InvoiceLineGrossAmt',

        'ItemProductID',
        'ItemURL',
        'ItemDescription',
        'ItemHSCode',
        'ItemOrigin',
        'ItemPieces',
        'ItemPiecePrice',
        'ItemGross',
        'ItemNet',
        'ItemFreightCur',
            'ItemFreight',
            'ItemInsuranceCur',
            'ItemInsurance',
            'ItemInland',
            'ItemInlandCur',
            'relForwarder',
            'relKlant',
            'Vervoermiddel',
            'Locatiecode',
            'Aangiftepunt',

            'Commoditycode',
            'StatistiekCode',
            'Omschrijving',
            'Bescheidcode1',
            'Bescheidcode2',
            'Bescheidcode3',
            'Bescheidcode4',
            'FreeReferenceField',
            'DualUse',
            'OptionalFieldD36',
            'OptionalFieldD37'

        ];

        foreach ($columns as $column) {
            $header[] = $column; //storecolumn in Header array
        }

        $stream->writeCsv($header, ';');

        $location = $this->_locationFactory->create();
        $location_collection = $location->getCollection(); // get Collection of Table data

        foreach ($batches as $batch) {
            /** @var $shipmentCollection ShipmentCollection */
            $shipmentCollection = $this->_shipmentCollectionFactory->create()->getShipmentOrdersDetails()->addFieldToFilter('main_table.awb_code', $batch['awb_code']);

            $shipments = $shipmentCollection->getItems();

            foreach ($shipments as $item) {
                $billingID = $this->_customerRepository->getById($item->getData('customer_id'))->getDefaultBilling();
                $address = $this->_addressFactory->create()->load($billingID);
                $chineseAddress = $this->_chineseAddressCollectionFactory->create()->addFieldToFilter('city', ['in' => $address->getData('city')])->toArray()['items'][0];

                $itemData = [];

                // column name must same as in your Database Table
                $itemData[] = $batch->getData('mawb_code');
                $itemData[] = date("d-m-Y", strtotime($batch->getData('updated_at')));
                $itemData[] = "NL"; //$item->getData('country_sender');
                $itemData[] = $batch->getData('batch_code');
                $itemData[] = $batch->getData('vehicle_tag');
                $itemData[] = 'Parcel';
                $itemData[] = $item->getData('f_shipment_id');
                $itemData[] = $item->getData('awb_code');

                $itemData[] = 'Europe Online Services BV';
                $itemData[] = 'Dennenlaan 9';
                $itemData[] = ' ';
                $itemData[] = '5271RE';
                $itemData[] = 'Sint-Michielsgestel';
                $itemData[] = 'NL';
                $itemData[] = 'onno.mallant@europeonlineservices.com';
                $itemData[] = '31682274151';
                $itemData[] = 'NL862289075B01';
                $itemData[] = 'NL862289075';

                $itemData[] = $address->getData('firstname') . " " . ($address->getData('middlename') ? $address->getData('middlename') . " " : "") . $address->getData('lastname');
                //$itemData[] = str_replace(";" , " ", explode("\n", $address->getData('street'))[1]);
                $itemData[] = str_replace(";" , " ", $address->getData('street'));
                $itemData[] = '';
                $itemData[] = $address->getData('postcode');
                $itemData[] = $chineseAddress['city_en'];

                $itemData[] = 'CN'; //$item->getData('country_receiver'); // Destination country of order instead of Country related to customer

                $itemData[] = ''; // Is Invoice nr.
                $itemData[] = $item->getData('webshop_order_nr'); // Is Invoice nr.
                $itemData[] = date("d-m-Y", strtotime($batch->getData('created_at')));; // TODO: Is day of order created in EOS system instead of Invoice date:
                $itemData[] = '';
                $itemData[] = 'DDU China'; //. ($item->getData('country_receiver') == 'CN' ? 'China' : 'Hong-Kong'); // TODO: Fix quick and dirty solution
                $itemData[] = $item->getData('webshop_currency');
                $itemData[] = $item->getData('webshop_order_total_price_net');
                $itemData[] = $item->getData('webshop_order_total_price_net') - $item->getData('webshop_order_total_price_gross');
                $itemData[] = $item->getData('webshop_order_total_price_gross');
                $itemData[] = $item->getData('product_price_net') * $item->getData('product_amount');
                $itemData[] = ($item->getData('product_price_net') * $item->getData('product_amount')) - ($item->getData('product_price_gross') * $item->getData('product_amount'));
                $itemData[] = $item->getData('product_price_gross') * $item->getData('product_amount');

                $itemData[] = '';
                $itemData[] = '';
                $itemData[] = $item->getData('product_title');
                $itemData[] = $item->getData('hs_code');
                $itemData[] = 'NL'; //$item->getData('country_sender');
                $itemData[] = $item->getData('product_amount');
                $itemData[] = ''; // round($item->getData('price'), 2); TODO: Unknown definition of ItemPiecePrice, is it gross/net price?
                $itemData[] = $item->getData('product_price_gross');
                $itemData[] = $item->getData('product_price_net');

                $itemData[] = 'EUR'; //$item->getData('webshop_currency');
                $itemData[] = $item->getData('webshop_order_costs_price_net');

                $itemData[] = '' ;
                $itemData[] = '' ;

                $itemData[] = '' ;
                $itemData[] = '' ;

                $itemData[] = 'EOS' ;
                $itemData[] = 'EOS' ;
                $itemData[] = $batch->getData('flight_code');
                $itemData[] = 'RSW' ;
                $itemData[] = '' ;

                $itemData[] = '' ;
                $itemData[] = '' ;
                $itemData[] = '' ;
                $itemData[] = '' ;
                $itemData[] = '' ;
                $itemData[] = '' ;
                $itemData[] = '' ;
                $itemData[] = '' ;
                $itemData[] = '' ;
                $itemData[] = '' ;
                $itemData[] = '' ;

                $stream->writeCsv($itemData, ';', ' ');
            }
        }

        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder

        $csvfilename = 'locator-import-' . $name . '.csv';
        return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
    }
}
