<?php
namespace Eos\Base\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 * @package Vendor\ModuleName\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), "1.0.8", "<")) {
            //Your upgrade script
        }
        if (version_compare($context->getVersion(), '1.0.9', '<')) {



            // $setup->getConnection()->renameTable($setup->getTable('eos_parcel'), $setup->getTable('eos_parcel'));
           /* $installer->getConnection()->addColumn(
                $installer->getTable('eos_shipment'),
                'm_order_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => '11',
                    'nullable'  => true,
                    'default' => 0,
                    'comment' => 'Magento Order ID'
                ]
            );*/
            /*
            $installer->getConnection()->addColumn(
                $installer->getTable('eos_shipment'),
                'total_weight',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length'    => '10,2',
                    'nullable'  => true,
                    'default'   => 0.00,
                    'comment' => 'Total weight'
                ]
            );*/
        }

        $this->createEntityUploadId($installer);

        $this->createEntityShipment($installer);
        $this->createEntityOrder($installer);
        $this->createEntityOrderDetails($installer);
        $this->createEntityParcel($installer);
        $this->createEntityParcelTemplate($installer);
        $this->createEntityCountry($installer);

        $this->createEntityHs($installer);
        $this->createEntityHsCategory($installer);
        $this->createEntityHsSubCategory($installer);
        $this->createEntityHsProduct($installer);
        $this->createEntityHsChina($installer);

        $this->createEntityWarehouse($installer);
        $this->createEntityBatch($installer);
        $this->createEntityBatchPallet($installer);



    }

    /**
     * Create tables for keeping track of many of each product a customer has on backorder
     * @param SchemaSetupInterface $installer
     * @throws \Zend_Db_Exception
     */
    protected function createEntityOrder(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable('eos_order')
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned'=>true, 'nullable'=>false, 'default' => '0'],
                'Customer Id'
            )
            ->addColumn(
                'shipment_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned'=>true, 'nullable'=>true, 'default' => '0'],
                'Shipment Id'
            )
            ->addColumn(
                'warehouse_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned'=>true, 'nullable'=>true, 'default' => '0'],
                'Warehosue Id'
            )
            ->addColumn(
                'webshop_currency',
                Table::TYPE_TEXT,
                5,
                ['nullable'=>true, 'default' => 'EUR'],
                'Shipment Id'
            )
            ->addColumn(
                'webshop_title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Webshop - Title'
            )->addColumn(
                'webshop_order_nr',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true,'default' => null],
                'Webshop - Order nr'
            )
            ->addColumn(
                'webshop_order_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                [
                    'nullable'  => true,
                    'default' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE
                ],
                'Invoice date'
            )
            ->addColumn(
                'webshop_order_total_price_net',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,2',
                ['nullable' => true,'default' => 0.00],
                'Webshop - Price'
            )
            ->addColumn(
                'webshop_order_total_price_gross',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,2',
                ['nullable' => true,'default' => 0.00],
                'Webshop - Price'
            )
            ->addColumn(
                'webshop_order_costs_price_net',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,2',
                ['nullable' => true,'default' => 0.00],
                'Webshop - Price'
            )
            ->addColumn(
                'webshop_order_costs_price_gross',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,2',
                ['nullable' => true,'default' => 0.00],
                'Webshop - Price'
            )
            ->addColumn(
                'webshop_order_discount_price_net',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,2',
                ['nullable' => true,'default' => 0.00],
                'Webshop - Price'
            )
            ->addColumn(
                'webshop_order_discount_price_gross',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,2',
                ['nullable' => true,'default' => 0.00],
                'Webshop - Price'
            )
            ->addColumn(
                'webshop_tracking_number',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true,'default' => null],
                'Webshop - Tracking Number'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true,'default' => 'Open'],
                'Order Status'
            )
            ->addColumn(
                'book_ind',
                Table::TYPE_INTEGER,
                1,
                ['unsigned'=>true, 'nullable'=>true, 'default' => 0],
                'Book Indicator'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )
            ->addForeignKey( // Add foreign key for table entity
                $installer->getFkName(
                    'eos_order', // New table
                    'customer_id', // Column in New Table
                    'customer_entity', // Reference Table
                    'entity_id' // Column in Reference table
                ),
                'customer_id', // New table column
                $installer->getTable('customer_entity'), // Reference Table
                'entity_id', // Reference Table Column
                // When the parent is deleted, delete the row with foreign key
                Table::ACTION_CASCADE
            )
            ->setComment('Eos Orders');

        $installer->getConnection()->createTable($table);
    }

    /**
     * Create tables for keeping track of many of each product a customer has on backorder
     * @param SchemaSetupInterface $installer
     */
    protected function createEntityShipment(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable('eos_shipment')
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned'=>true, 'nullable'=>false, 'default' => '0'],
                'Customer Id'
            )
            ->addColumn(
                'f_shipment_id',
                Table::TYPE_TEXT,
                55,
                ['nullable'=>true, 'default' => '0'],
                'Functional shipment ID: EOS00000001'
            )
            ->addColumn(
                'awb_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'AWB Number'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true,'default' => 'Open'],
                'Order Status'
            )
            ->addColumn(
                'total_weight',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,2',
                ['nullable' => true,'default' => 0.00],
                'Consolidated total weight'
            )
            ->addColumn(
                'originCode',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                55,
                ['nullable' => true,'default' => 'Open'],
                'SF Response: OriginCode'
            )->addColumn(
                'destCode',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                55,
                ['nullable' => true,'default' => 'Open'],
                'SF Response: destCode'
            )->addColumn(
                'printUrl',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true,'default' => 'Open'],
                'SF Response: printUrl'
            )->addColumn(
                'invoiceUrl',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true,'default' => 'Open'],
                'SF Response: invoiceUrl'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )
            ->addForeignKey( // Add foreign key for table entity
                $installer->getFkName(
                    'eos_shipment', // New table
                    'customer_id', // Column in New Table
                    'customer_entity', // Reference Table
                    'entity_id' // Column in Reference table
                ),
                'customer_id', // New table column
                $installer->getTable('customer_entity'), // Reference Table
                'entity_id', // Reference Table Column
                // When the parent is deleted, delete the row with foreign key
                Table::ACTION_CASCADE
            )
            ->setComment('Eos Orders');

        $installer->getConnection()->createTable($table);
    }

    /**
     * Create tables for keeping track of many of each product a customer has on backorder
     * @param SchemaSetupInterface $installer
     */
    protected function createEntityHs(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable('eos_hs')
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'product_tax_nr',
                Table::TYPE_BIGINT,
                55,
                ['unsigned'=>true, 'nullable'=>false],
                'Customer Id'
            )
            ->addColumn(
                'hs_code',
                Table::TYPE_INTEGER,
                55,
                ['unsigned'=>true, 'nullable'=>true],
                'HS Code'
            )
            ->addColumn(
                'category_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned'=>true, 'nullable'=>true, 'default' => '0'],
                'Shipment Id'
            )
            ->addColumn(
                'subcategory_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned'=>true, 'nullable'=>true, 'default' => '0'],
                'Subcategory ID'
            )
            ->addColumn(
                'volume_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                55,
                ['nullable' => true],
                'Product Title'
            )
            ->setComment('Eos Product Group');

        $installer->getConnection()->createTable($table);
    }
    /**
     * Create tables for keeping track of many of each product a customer has on backorder
     * @param SchemaSetupInterface $installer
     */
    protected function createEntityHsProduct(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable('eos_hs_product')
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'product_tax_nr',
                Table::TYPE_BIGINT,
                55,
                ['unsigned'=>true, 'nullable'=>true, 'default' => '0'],
                'Shipment Id'
            )
            ->addColumn(
                'product_title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Product Title'
            )
            ->addColumn(
                'lang',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                3,
                ['nullable' => true],
                'Language'
            )

            ->setComment('Eos Product Categories');

        $installer->getConnection()->createTable($table);
    }

    /**
     * Create tables for keeping track of many of each product a customer has on backorder
     * @param SchemaSetupInterface $installer
     */
    protected function createEntityHsCategory(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable('eos_hs_category')
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'category_id',
                Table::TYPE_INTEGER,
                55,
                ['unsigned'=>true, 'nullable'=>true, 'default' => '0'],
                'Shipment Id'
            )
            ->addColumn(
                'category_title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                55,
                ['nullable' => true],
                'Category Title'
            )
            ->addColumn(
                'lang',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                3,
                ['nullable' => true],
                'Language'
            )

            ->setComment('Eos Product Categories');

        $installer->getConnection()->createTable($table);
    }
    /**
     * Create tables for keeping track of many of each product a customer has on backorder
     * @param SchemaSetupInterface $installer
     */
    protected function createEntityHsSubCategory(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable('eos_hs_subcategory')
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'category_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned'=>true, 'nullable'=>true, 'default' => '0'],
                'Subcategory id'
            )
            ->addColumn(
                'subcategory_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned'=>true, 'nullable'=>true, 'default' => '0'],
                'Subcategory id'
            )
            ->addColumn(
                'subcategory_title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                55,
                ['nullable' => true],
                'Subcategory title'
            )
            ->addColumn(
                'lang',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                3,
                ['nullable' => true],
                'Language'
            )

            ->setComment('Eos Hs SubCategories');

        $installer->getConnection()->createTable($table);
    }
    /**
     * Create tables for keeping track of many of each product a customer has on backorder
     * @param SchemaSetupInterface $installer
     */
    protected function createEntityHsChina(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable('eos_hs_china')
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'product_tax_nr',
                Table::TYPE_BIGINT,
                55,
                ['unsigned'=>true, 'nullable'=>true, 'default' => '0'],
                'Product Tax Number'
            )
            ->addColumn(
                'volume_min',
                Table::TYPE_INTEGER,
                null,
                ['unsigned'=>true, 'nullable'=>false, 'default' => '0'],
                'Min volume'
            )
            ->addColumn(
                'volume_max',
                Table::TYPE_INTEGER,
                null,
                ['unsigned'=>true, 'nullable'=>false, 'default' => '0'],
                'Max volume'
            )
            ->addColumn(
                'tax_rate',
                Table::TYPE_INTEGER,
                null,
                ['unsigned'=>true, 'nullable'=>false, 'default' => '0'],
                'Tax rate'
            )
            ->addColumn(
                'calculate_ind',
                Table::TYPE_INTEGER,
                null,
                ['unsigned'=>true, 'nullable'=>false, 'default' => '0'],
                'Calculation indicator'
            )

            ->setComment('Eos Product Categories');

        $installer->getConnection()->createTable($table);
    }
    /**
     * Create tables for keeping track of many of each product a customer has on backorder
     * @param SchemaSetupInterface $installer
     */
    protected function createEntityOrderDetails(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable('eos_order_details')
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned'=>true, 'nullable'=>false, 'default' => '0'],
                'Order Id'
            )
            ->addColumn(
                'product_brand',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                55,
                ['nullable' => true],
                'Brand'
            )
            ->addColumn(
                'product_tax_nr',
                Table::TYPE_INTEGER,
                null,
                ['unsigned'=>true, 'nullable'=>false, 'default' => '0'],
                'Product Code'
            )
            ->addColumn(
                'product_title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                55,
                ['nullable' => true],
                'Title'
            )

            ->addColumn(
                'product_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                5,
                ['nullable' => true],
                'Amount'
            )
            ->addColumn(
                'product_price_net',
                Table::TYPE_DECIMAL,
                '10,2',
                ['nullable' => true,'default' => 0.00],
                'Price'
            )
            ->addColumn(
                'product_price_gross',
                Table::TYPE_DECIMAL,
                '10,2',
                ['nullable' => true,'default' => 0.00],
                'Price'
            )
            ->addColumn(
                'product_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                55,
                ['nullable' => true],
                'Product Type'
            )
            ->addColumn(
                'product_tax',
                Table::TYPE_INTEGER,
                null,
                ['unsigned'=>true, 'nullable'=>false, 'default' => '0'],
                'Product Tax'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )
            ->addForeignKey( // Add foreign key for table entity
                $installer->getFkName(
                    'eos_order_details', // New table
                    'order_id', // Column in New Table
                    'eos_order', // Reference Table
                    'entity_id' // Column in Reference table
                ),
                'order_id', // New table column
                $installer->getTable('eos_order'), // Reference Table
                'entity_id', // Reference Table Column
                // When the parent is deleted, delete the row with foreign key
                Table::ACTION_CASCADE
            )/*
            ->addForeignKey( // Add foreign key for table entity
                $installer->getFkName(
                    'eos_order_details', // New table
                    'product_tax_nr', // Column in New Table
                    'eos_hs', // Reference Table
                    'product_tax_nr' // Column in Reference table
                ),
                'product_tax_nr', // New table column
                $installer->getTable('eos_hs'), // Reference Table
                'entity_id', // Reference Table Column
                // When the parent is deleted, delete the row with foreign key
                Table::ACTION_CASCADE
            )*/
            ->setComment('Eos Order - Product Group');

        $installer->getConnection()->createTable($table);
    }
    /**
     * Create tables for keeping track of many of each product a customer has on backorder
     * @param SchemaSetupInterface $installer
     */
    protected function createEntityParcel(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable('eos_parcel')
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'tracking_number',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Tracking number'
            )
            ->addColumn(
                'weight',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,2',
                ['nullable' => true,'default' => 0.00],
                'Webshop - Price'
            )
            ->addColumn(
                'length',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,2',
                ['nullable' => true,'default' => 0.00],
                'Webshop - Price'
            )
            ->addColumn(
                'height',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,2',
                ['nullable' => true,'default' => 0.00],
                'Webshop - Price'
            )
            ->addColumn(
                'width',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,2',
                ['nullable' => true,'default' => 0.00],
                'Webshop - Price'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false],
                'Created At'
            )
            ->addColumn(
                'amount_parcels',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                255,
                ['nullable' => false],
                'Amount of parcels'
            )

            ->setComment('Eos parcels');

        $installer->getConnection()->createTable($table);
    }
    /**
     * Create tables for keeping track of many of each product a customer has on backorder
     * @param SchemaSetupInterface $installer
     */
    protected function createEntityParcelTemplate(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable('eos_parcel_template')
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Template title'
            )
            ->addColumn(
                'length',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,2',
                ['nullable' => true,'default' => 0.00],
                'Webshop - Price'
            )
            ->addColumn(
                'height',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,2',
                ['nullable' => true,'default' => 0.00],
                'Webshop - Price'
            )
            ->addColumn(
                'width',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,2',
                ['nullable' => true,'default' => 0.00],
                'Webshop - Price'
            )
            ->setComment('Eos parcel templates');

        $installer->getConnection()->createTable($table);
    }
    /**
     * Create tables for keeping track of many of each product a customer has on backorder
     * @param SchemaSetupInterface $installer
     */
    protected function createEntityCountry(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable('eos_country')
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'country_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'Functional ID'
            )
            ->addColumn(
                'country_lang',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                5,
                ['nullable' => false],
                'language'
            )
            ->addColumn(
                'country_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                2,
                ['nullable' => false],
                'Type'
            )
            ->addColumn(
                'country_title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                55,
                ['nullable' => false],
                'Title'
            )
            ->addColumn(
                'country_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                2,
                ['nullable' => false],
                'Code'
            )

            ->setComment('Eos Countries');

        $installer->getConnection()->createTable($table);
    }

    /**
     * Create tables for keeping track of many of each product a customer has on backorder
     * @param SchemaSetupInterface $installer
     */
    protected function createEntityBatch(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable('eos_batch')
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'batch_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                11,
                ['nullable' => false],
                'Functional ID'
            )
            ->addColumn(
                'flight_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                11,
                ['nullable' => true],
                'Flight code'
            )
            ->addColumn(
                'warehouse_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'Employee'
            )
            ->addColumn(
                'user_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'Employee'
            )
            ->addColumn(
                'vehicle_tag',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                21,
                ['nullable' => false],
                'SF Vehicle Tag'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                21,
                ['nullable' => false],
                'Status'
            )
            ->addColumn(
                'mawb_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                21,
                ['nullable' => true],
                'MAWB Code'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )

            ->setComment('Eos Batch');

        $installer->getConnection()->createTable($table);
    }

    /**
     * Create tables for keeping track of many of each product a customer has on backorder
     * @param SchemaSetupInterface $installer
     */
    protected function createEntityBatchPallet(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable('eos_batch_pallet')
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'batch_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'Foreign Batch ID'
            )
            ->addColumn(
                'awb_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                21,
                ['nullable' => false],
                'AWB Code'
            )
            ->addColumn(
                'pallet_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                21,
                ['nullable' => false],
                'SF Vehicle Tag'
            )

            ->setComment('Eos Batch Pallets');

        $installer->getConnection()->createTable($table);
    }
    /**
     * Create tables for keeping track of many of each product a customer has on backorder
     * @param SchemaSetupInterface $installer
     */
    protected function createEntityWarehouse(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable('eos_warehouse')
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                51,
                ['nullable' => false],
                'Warehouse Title'
            )
            ->addColumn(
                'address',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Address'
            )
            ->addColumn(
                'country_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                3,
                ['nullable' => false],
                'Country Code'
            )
            ->addColumn(
                'country_title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                21,
                ['nullable' => false],
                'Country Title'
            )

            ->setComment('Eos Warehouses');

        $installer->getConnection()->createTable($table);
    }

    /**
     * Create tables for keeping track of many of each product a customer has on backorder
     * @param SchemaSetupInterface $installer
     */
    protected function createEntityUploadId(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable('eos_upload_id')
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned'=>true, 'nullable'=>false, 'default' => '0'],
                'Customer ID'
            )
            ->addColumn(
                'image_type',
                Table::TYPE_TEXT,
                null,
                ['nullable'=>false],
                'ID Card can be Frontside or Backside'
            )
            ->addColumn(
                'awb_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'AWB Code'
            )
            ->addColumn(
                'code',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned'=>true, 'nullable'=>false, 'default' => '0'],
                'SF Response: Code'
            )
            ->addColumn(
                'message',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'SF Response: Message'
            )
            ->addColumn(
                'result',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                10,
                ['nullable' => false],
                'SF Response: Result'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )

            ->setComment('Eos Upload ID card Image');

        $installer->getConnection()->createTable($table);
    }
}
