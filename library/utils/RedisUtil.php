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
        return 'wechat_mall_access_token';
    }

}