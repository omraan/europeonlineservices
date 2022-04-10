<?php
namespace Eos\Base\Model\ResourceModel;
class UploadId extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('eos_upload_id', 'entity_id');
    }
}
