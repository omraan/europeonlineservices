<?php
namespace Eos\Base\Setup\Patch\Data;

use Eos\Base\Model\Warehouse as EntityModel;
use Eos\Base\Model\ResourceModel\Warehouse as EntityResourceModel;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddWarehouses implements DataPatchInterface
{
    /** @var ModuleDataSetupInterface */
    protected $moduleDataSetup;

    /** @var EntityResourceModel */
    protected $entityResourceModel;

    /** @var EntityModel */
    protected $entityFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EntityResourceModel $entityResourceModel,
        EntityModel $entityFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->entityResourceModel = $entityResourceModel;
        $this->entityFactory = $entityFactory;
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     * Example of implementation:
     *
     * [
     *      \Vendor_Name\Module_Name\Setup\Patch\Patch1::class,
     *      \Vendor_Name\Module_Name\Setup\Patch\Patch2::class
     * ]
     *
     * @return string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Run code inside patch
     * If code fails, patch must be reverted, in case when we are speaking about schema - then under revert
     * means run PatchInterface::revert()
     *
     * If we speak about data, under revert means: $transaction->rollback()
     *
     * @return $this
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function apply()
    {

        $setup = $this->moduleDataSetup;
        $setup->startSetup();

        $table = $setup->getTable('eos_warehouse');

        $setup->getConnection()->insert($table, [
            'entity_id' => 1,
            'title' => 'Warehouse Rijswijk',
            'address' => 'SomeStreet 12a 1234AA Rijswijk',
            'country_title' => 'The Netherlands',
            'country_code' => 'NL'
        ]);

        $setup->getConnection()->insert($table, [
            'entity_id' => 2,
            'title' => 'Warehouse Hamburg',
            'address' => 'SomeStreet 12a 1234AA Hamburg',
            'country_title' => 'Germany',
            'country_code' => 'DE'
        ]);

        $setup->endSetup();
    }
}
