<?php
/**
 * Created by PhpStorm.
 * User: wang.haibo
 * Date: 2018/8/3
 * Time: 11:00
 */

namespace library\utils;


class RedisUtil
{

    /**
     * 获取 微信 access token redis key
     *
     * @author wang.haibo
     * @date   2018-08-03
     * @return string
     */
    public static function getWechatAccessTokenKey() {
        return 'wechat_shop_access_token';
    }

    /**
     * 获取 微信 订单号 锁 redis key
     *
     * @author wang.haibo
     * @date   2018-08-03
     * @param  string $_strOrderId 订单号
     * @return string
     */
    public static function getWechatOrderIdLockKey($_strOrderId) {
        return 'wechat:shop:order:id:lock:'. $_strOrderId;
    }

}