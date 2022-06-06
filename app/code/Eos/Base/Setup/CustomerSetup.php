<?php

namespace Eos\Base\Setup;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Setup\Context;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;

class CustomerSetup extends EavSetup
{

    protected $eavConfig;

    private $eavSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $setup,
        Context                  $context,
        CacheInterface           $cache,
        CollectionFactory        $attrGroupCollectionFactory,
        Config                   $eavConfig,
        EavSetupFactory $eavSetupFactory
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        parent:: __construct($setup, $context, $cache, $attrGroupCollectionFactory);
    }

    public function installAttributes($customerSetup)
    {
        $this->installCustomerAttributes($customerSetup);
        $this->installCustomerAddressAttributes($customerSetup);
    }

    public function installCustomerAttributes($customerSetup)
    {
        //$eavSetup = $this->eavSetupFactory->create(['setup' => $customerSetup]);
        $customerSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'customer_name_en',
            [
                'type'         => 'varchar',
                'label'        => 'Customer English Name',
                'input'        => 'text',
                'required'     => false,
                'visible'      => true,
                'user_defined' => true,
                'position'     => 999,
                'system'       => 0,
            ]
        );
        $customerSetup->addAttributeToSet(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            null,
            'customer_name_en');

        // more used_in_forms ['adminhtml_checkout','adminhtml_customer','adminhtml_customer_address','customer_account_edit','customer_address_edit','customer_register_address']
        $customerSetup->getEavConfig()
            ->getAttribute('customer','customer_name_en')
            ->setData('is_user_defined', 1)
            ->setData('default_value', '')
            ->setData('used_in_forms', ['adminhtml_customer','customer_account_edit','customer_account_create'])
            ->save();

    }

    public function installCustomerAddressAttributes($customerSetup)
    {
        $customerSetup->addAttribute('customer_address',
            'chinese_address_street',
            [
                'label' => 'Chinese Address street',
                'system' => 0,
                'user_defined' => true,
                'position' => 100,
                'sort_order' => 100,
                'visible' => true,
                'default_value' => '',
                'note' => '',
                'type' => 'varchar',
                'input' => 'text',

            ]
        );

        $customerSetup->addAttributeToSet(
            'customer_address',
            2,
            null,
            'chinese_address_street');

        $customerSetup->getEavConfig()
            ->getAttribute('customer_address', 'chinese_address_street')
            ->setData('is_user_defined', 1)
            ->setData('default_value', '')
            ->setData('used_in_forms', ['adminhtml_customer_address', 'customer_register_address', 'customer_address_edit'])
            ->save();
    }

    public function getEavConfig() {
        return $this->eavConfig;
    }
}
