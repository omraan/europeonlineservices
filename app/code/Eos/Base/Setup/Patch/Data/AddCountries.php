<?php
namespace Eos\Base\Setup\Patch\Data;

use Eos\Base\Model\Country as CountryModel;
use Eos\Base\Model\ResourceModel\Country as CountryResourceModel;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddCountries implements DataPatchInterface
{
    /** @var ModuleDataSetupInterface */
    protected $moduleDataSetup;

    /** @var CountryResourceModel */
    protected $countryResourceModel;

    /** @var CountryModel */
    protected $countryFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CountryResourceModel $countryResourceModel,
        CountryModel $countryFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->countryResourceModel = $countryResourceModel;
        $this->countryFactory = $countryFactory;
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

        $table = $setup->getTable('eos_country');

        $setup->getConnection()->insert($table, [
                'entity_id' => 1,
                'country_id' => 1,
                'country_lang' => 'en',
                'country_type' => 's',
                'country_title' => 'The Netherlands',
                'country_code' => 'NL'
            ]);
        $setup->getConnection()->insert($table, [
                'entity_id' => 2,
                'country_id' => 2,
                'country_lang' => 'en',
                'country_type' => 'r',
                'country_title' => 'China',
                'country_code' => 'CN'
            ]);
        $setup->getConnection()->insert($table, [
                'entity_id' => 3,
                'country_id' => 3,
                'country_lang' => 'en',
                'country_type' => 'r',
                'country_title' => 'Hong-kong',
                'country_code' => 'HK'
            ]);

        $setup->endSetup();
    }
}
