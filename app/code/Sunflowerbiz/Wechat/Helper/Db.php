<?php


namespace Sunflowerbiz\Wechat\Helper;
use \Sunflowerbiz\Wechat\Helper\ObjectManager as Sunflowerbiz_OM;

/**
 * Class Db
 *
 * @package Sunflowerbiz\Wechat\Helper
 */
class Db
{
    /**
     * Retrieve connection to resource specified by $resourceName
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws \DomainException
     * @codeCoverageIgnore
     */
    public static function db_connect(){
        return Sunflowerbiz_OM::getObjectManager()->get('\Magento\Framework\App\ResourceConnection')->getConnection();
    }
}
