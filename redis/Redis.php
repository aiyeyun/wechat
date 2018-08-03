<?php
/**
 * Created by PhpStorm.
 * User: wang.haibo
 * Date: 2018/8/2
 * Time: 17:00
 */

namespace redis;


use service\Bnw;

class Redis extends \service\Redis
{

    /**
     * 获取 Redis 实例对象
     *
     * @author wang.haibo
     * @date   2018-08-02
     * @return \Redis
     */
    public static function getRedisInstance() {
        return \service\Redis::getInstance(
            Bnw::$app->config['redis']['hostname'],
            Bnw::$app->config['redis']['port'],
            Bnw::$app->config['redis']['password'],
            Bnw::$app->config['redis']['database']
        )->redis;
    }

    /**
     * 设置 缓存
     *
     * @author wang.haibo
     * @date   2018-08-03
     * @param  string $_strKey
     * @param  object $_objVal
     * @param  integer $_intExpires 过期时间
     * @return bool
     */
    public static function setStringCache($_strKey, $_objVal, $_intExpires = 0) {
        is_array($_objVal) ? $_objVal = json_encode($_objVal) : false;
        $redis = self::getRedisInstance();
        return $redis->set($_strKey, $_objVal, $_intExpires);
    }

    /**
     * 获取 缓存
     *
     * @author wang.haibo
     * @date   2018-08-03
     * @param  string $_strKey
     * @return string
     */
    public static function getStringCache($_strKey) {
        $redis = self::getRedisInstance();
        $obj = $redis->get($_strKey);
        $obj = json_decode($obj, true) ? json_decode($obj, true) : $obj;
        return $obj;
    }

}