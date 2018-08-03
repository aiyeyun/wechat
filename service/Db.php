<?php
/**
 * Created by PhpStorm.
 * User: wang.haibo
 * Date: 2018/8/2
 * Time: 12:18
 */

namespace service;


use Medoo\Medoo;

class Db
{
    /** @var $_instance Medoo */
    private static $_instance;

    /**
     * 私有构造函数，防止外界实例化对象
     * Db constructor.
     *
     * @author wang.haibo
     * @date   2018-08-02
     */
    private function __construct() {

    }

    /**
     * 私有克隆函数，防止外办克隆对象
     *
     * @author wang.haibo
     * @date   2018-08-02
     */
    private function __clone() {
        trigger_error('Clone is not allow!',E_USER_ERROR);
    }

    /**
     * 静态方法，单例统一访问入口
     *
     * @author wang.haibo
     * @date   2018-08-02
     * @param  array $_aryDb 数据库
     * @param  string $_strTableName 表名
     * @throws \Exception
     * @return Medoo
     */
    static public function getInstance(array $_aryDb = [], $_strTableName) {
        if (!$_aryDb || !$_strTableName) {
            Bnw::$app->logger->error('数据库配置错误, 数据库配置项: ', $_aryDb);
            Bnw::$app->logger->error('数据库配置错误, 表名: '. $_strTableName);
            throw new \Exception('数据库配置错误, 表名: '. $_strTableName);
        }

        if (!is_array($_aryDb) || !isset($_aryDb['type']) || !isset($_aryDb['database']) || !isset($_aryDb['host'])
            || !isset($_aryDb['port']) || !isset($_aryDb['username']) || !isset($_aryDb['password'])
        ) {
            exit('db config 缺少配置项');
        }

        if(! (self::$_instance instanceof \Medoo\Medoo) ) {
            try{
                $aryDbConfig = [
                    // 必须配置项
                    'database_type' => $_aryDb['type'],
                    'database_name' => $_aryDb['database'],
                    'server'        => $_aryDb['host'],
                    'username'      => $_aryDb['username'],
                    'password'      => $_aryDb['password'],
                    'port'          => $_aryDb['port'],
                    'charset'       => isset($_aryDb['charset']) ? $_aryDb['charset'] : 'utf8',
                    // 可选，定义表的前缀
                    'prefix'        => isset($_aryDb['prefix']) ? $_aryDb['prefix'] : null,
                ];
                self::$_instance = new Medoo($aryDbConfig);
            }catch (\Exception $exception) {
                exit($exception->getMessage().' config: '. json_encode($aryDbConfig));
            }
        }
        return self::$_instance;
    }

}