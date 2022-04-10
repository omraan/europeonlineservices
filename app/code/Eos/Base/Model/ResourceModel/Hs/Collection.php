<?php
namespace Eos\Base\Model\ResourceModel\Hs;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Eos\Base\Model\Hs',
            'Eos\Base\Model\ResourceModel\Hs'
        );
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        /*
                        $this->getSelect()->joinLeft(
                            ['eos_hs_category' => $this->getTable('eos_hs_category')], //2nd table name by which you want to join
                            'eos_hs_category.category_id = main_table.category_id', // common column which available in both table
                           ['category_title','category_lang'=>'lang'] // '*' define that you want all column of 2nd table. if you want some particular column then you can define as ['column1','column2']
                        );
                        $this->getSelect()->joinLeft(
                            ['eos_hs_subcategory' => $this->getTable('eos_hs_subcategory')],
                            'eos_hs_subcategory.subcategory_id = main_table.subcategory_id and eos_hs_subcategory.category_id = main_table.category_id',
                            ['subcategory_title','subcategory_lang'=>'lang']
                        );


                        $this->getSelect()->joinLeft(
                            ['eos_hs_product' => $this->getTable('eos_hs_product')],
                            'eos_hs_product.product_tax_nr = main_table.product_tax_nr',
                            ['product_title','product_lang'=>'lang']
                        );*/



        /*
                $this->getSelect()->joinLeft(
                    ['eos_hs_china' => $this->getTable('eos_hs_china')],
                    'eos_hs_china.product_tax_nr = main_table.product_tax_nr',
                    ['volume_min','volume_max','tax_rate','calculate_ind']
                );
        */


    }


    public function getCategories()
    {
        $this->getSelect()->joinLeft(
            ['eos_hs_category' => $this->getTable('eos_hs_category')], //2nd table name by which you want to join
            'eos_hs_category.category_id = main_table.category_id', // common column which available in both table
            ['category_title','category_lang'=>'lang'] // '*' define that you want all column of 2nd table. if you want some particular column then you can define as ['column1','column2']
        );
        return $this;
    }

    public function getSubCategories()
    {
        $this->getSelect()->joinLeft(
            ['eos_hs_subcategory' => $this->getTable('eos_hs_subcategory')],
            'eos_hs_subcategory.subcategory_id = main_table.subcategory_id and eos_hs_subcategory.category_id = main_table.category_id',
            ['subcategory_title','subcategory_lang'=>'lang']
        );
        return $this;
    }

    public function getSubCategoriesRight()
    {
        $this->getSelect()->joinRight(
            ['eos_hs_subcategory' => $this->getTable('eos_hs_subcategory')],
            'eos_hs_subcategory.subcategory_id = main_table.subcategory_id and eos_hs_subcategory.category_id = main_table.category_id',
            ['subcategory_title','subcategory_lang'=>'lang']
        );
        return $this;
    }
    public function getProducts()
    {
        $this->getSelect()->joinLeft(
            ['eos_hs_product' => $this->getTable('eos_hs_product')],
            'eos_hs_product.product_tax_nr = main_table.product_tax_nr',
            ['product_title','product_lang'=>'lang']
        );
        return $this;
    }

}
