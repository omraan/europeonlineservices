<?php
namespace Eos\Base\Model\ResourceModel\Batch;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Eos\Base\Model\Batch',
            'Eos\Base\Model\ResourceModel\Batch'
        );
    }

    public function getBatches()
    {
        $this->getSelect()->joinLeft(
            ['eos_batch_pallet' => $this->getTable('eos_batch_pallet')], //2nd table name by which you want to join
            'eos_batch_pallet.batch_id = main_table.entity_id', // common column which available in both table
            [
                '*',
                'batch_id' => 'eos_batch_pallet.entity_id'
            ]
        );

        return $this;
    }

}
