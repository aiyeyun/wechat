<?php
/**
 * Created by PhpStorm.
 * User: wang.haibo
 * Date: 2018/8/2
 * Time: 17:02
 */

namespace service;


class Redis
{

    /**
     * Redis的连接句柄
     * @var \Redis
     */
    public $redis;

    /**
     * 静态变量保存全局实例
     * @var $_instance \Redis
     */
    private static $_instance = null;

    /**
     * 私有构造函数，防止外界实例化对象
     * Redis constructor.
     *
     * @param  string  $_strHost 地址
     * @param  integer $_intPort 端口
     * @param  string $_strAuth 密码
     * @param  string $_strDb 数据库
     * @author wang.haibo
     * @date   2018-08-02
     */
    private function __construct($_strHost, $_intPort, $_strAuth, $_strDb) {
        $this->redis = new \Redis();
        $this->redis->connect($_strHost, $_intPort);
        $_strAuth ? $this->redis->auth($_strAuth) : null;
        $_strDb ? $this->redis->select($_strDb) : null;
    }

    //私有克隆函数，防止外办克隆对象
    private function __clone() {
        trigger_error('Clone is not allow!',E_USER_ERROR);
    }

    /**
     * 静态方法，单例统一访问入口
     *
     * @author wang.haibo
     * @date   2018-08-02
     * @param  string $_strHost
     * @param  integer $_intPort
     * @param  string $_strAuth
     * @param  string $_strDb
     * @return \Redis|Redis
     */
    static public function getInstance($_strHost, $_intPort, $_strAuth = '', $_strDb = '') {
        if(! (self::$_instance instanceof self) ) {
            self::$_instance = new self($_strHost, $_intPort, $_strAuth, $_strDb);
        }
        return self::$_instance;
    }

}