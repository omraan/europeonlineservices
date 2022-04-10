<?php

namespace Sunflowerbiz\Wechat\Helper;
// Sunflowerbiz\Wechat\Helper\ObjectManager::getObjectManager()
use \Magento\Framework\App\ObjectManager as MAGE_OM;

/**
 * Class ObjectManager
 *
 * @package Sunflowerbiz\Wechat\Helper
 */
class ObjectManager
{
    /**
     * @return \Magento\Framework\App\ObjectManager
     */
    public static function getObjectManager(){
        return MAGE_OM::getInstance();
        }
}