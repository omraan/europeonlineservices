<?php

namespace Sunflowerbiz\Wechat\Helper;
use \Sunflowerbiz\Wechat\Helper\ObjectManager as Sunflowerbiz_OM;

/**
 * Class Customer
 *
 * @package Sunflowerbiz\Wechat\Helper
 */
class Customer
{
    /**
     * @return \Magento\Customer\Model\Session
     */
    public static function getSession(){
        return Sunflowerbiz_OM::getObjectManager()->get('Magento\Customer\Model\Session');
    }

    /**
     * @return bool
     */
    public static function isLoggedIn(){

        return self::getSession()->isLoggedIn();
    }

    /**
     * @return bool|int
     */
    public static function getCustID(){
        return self::isLoggedIn() ? (int)self::getSession()->getCustomerId() : false;
    }

}