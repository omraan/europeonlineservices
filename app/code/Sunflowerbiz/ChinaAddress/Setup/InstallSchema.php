<?php

namespace Sunflowerbiz\ChinaAddress\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
  $tableName = $installer->getTable('address_cn_data');
        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableName) != true) {


            // Create tutorial_simplenews table
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'record id'
                )
                ->addColumn(
                    'province',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Province'
                )
                 ->addColumn(
                    'city',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'City'
                )
                ->addColumn(
                    'region',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Region'
                )
                ->addColumn(
                    'province_en',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Province EN'
                )
                ->addColumn(
                    'city_en',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'City EN'
                );
            $installer->getConnection()->createTable($table);

			//$sql=str_replace('`address_cn_data`','`'.$tableName.'`',file_get_contents(dirname(__FILE__).'/address_cn_data.sql');



        }

        $installer->endSetup();
    }
}
