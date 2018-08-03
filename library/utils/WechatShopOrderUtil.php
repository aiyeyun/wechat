<?php
/**
 * Created by PhpStorm.
 * User: wang.haibo
 * Date: 2018/8/3
 * Time: 13:03
 */

namespace library\utils;


class WechatShopOrderUtil
{

    /** 等待发货 */
    const STATUS_WAIT = 1;

    /** 发货成功 */
    const STATUS_SUCCESS = 2;

    /** 发货失败 */
    const STATUS_FAIL = 3;

    /**
     * 获取商品详情
     *
     * @author wang.haibo
     * @date   2018-08-03
     * @param  integer $_intPrice
     * @return array
     */
    public static function getGoodsDetails($_intPrice) {
        $aryData = [
            1000 => [ 'order_type' => 31, 'order_nick' => '月卡' ],
            2500 => [ 'order_type' => 183, 'order_nick' => '半年卡' ],
            3000 => [ 'order_type' => 366, 'order_nick' => '年卡' ],
            9800 => [ 'order_type' => 36600, 'order_nick' => '永久卡' ],
        ];

        if (isset($aryData[$_intPrice])) {
            return $aryData[$_intPrice];
        }
        return [ 'order_type' => 0, 'order_nick' => '测试' ];
    }

}