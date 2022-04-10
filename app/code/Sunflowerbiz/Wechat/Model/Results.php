<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sunflowerbiz\Wechat\Model;
use \Sunflowerbiz\Wechat\Model\Database;

class Results extends Database
{
    /**
     *
     * 检测签名
     */
    public function CheckSign()
    {
        if (!$this->IsSignSet()) {
            throw new \Magento\Framework\Validator\Exception(__('Payment error.' ."Sign Error!"));
        }

        $sign = $this->MakeSign();
        if ($this->GetSign() == $sign) {
            return true;
        }
			 throw new \Magento\Framework\Validator\Exception(__('Payment error.' ."Sign Error!"));
    }

    /**
     *
     * 使用数组初始化
     * @param array $array
     */
    public function FromArray($array)
    {
        $this->values = $array;
    }

    /**
     *
     * 使用数组初始化对象
     * @param array $array
     */
    public static function InitFromArray($array, $noCheckSign = false)
    {
        $obj = new self();
        $obj->FromArray($array);
        if ($noCheckSign == false) {
            $obj->CheckSign();
        }
        return $obj;
    }

    /**
     *
     * 设置参数
     * @param string $key
     * @param string $value
     */
    public function SetParamsData($key, $value)
    {
        $this->values[$key] = $value;
    }

    /**
     * 将xml转为array
     * @param string $xml
     * @throws Exception
     */
    public function Init($xml)
    {
        $this->FromXml($xml);
		
        if ($this->values['return_code'] != 'SUCCESS') {
            return $this->GetValues();
        }
        $this->CheckSign();
        return $this->GetValues();
    }

    /**
     * @param $data
     * 对订单的逻辑操作
     * [appid] => wx797f473c3f89be87
     * [attach] => test
     * [bank_type] => CFT
     * [cash_fee] => 1
     * [fee_type] => CNY
     * [is_subscribe] => Y
     * [mch_id] => 1262734601
     * [nonce_str] => ph948qdhj9tyn5a6rtd1o16fffebq1fj
     * [openid] => o3xCAuCKr4LOp2qzHFLp8K2yYli8
     * [out_trade_no] => 126273460120150916131753
     * [result_code] => SUCCESS
     * [return_code] => SUCCESS
     * [sign] => D40BFEAE4CD5F59FF0A599BF7CC84931
     * [time_end] => 20150916135217
     * [total_fee] => 1
     * [trade_type] => JSAPI
     * [transaction_id] => 1006310366201509160896310681
     */
    public function NotifyProcess($data)
    {
        $order_increment_id = $out_trade_no = $data['out_trade_no'];      // order id

        $transaction_id = $data['transaction_id'];              // transaction id

        $total_fee = $data['total_fee'];                        // order total

        $result_code = $data['result_code'];                    // result status

        // check status
        if ($result_code !== 'SUCCESS') {
            $message = 'Order: ' . $order_increment_id . ', Wechat notified, Pay failed!';
            $data['message'] = $message;
			 throw new \Magento\Framework\Validator\Exception(__('Payment error.' . $message ));
        }


		//log

    }


    /**
     * notify to Wechat server
     */
    function ReplyNotify()
    {
        $this->getResponse()->setBody('SUCCESS');
    }
}