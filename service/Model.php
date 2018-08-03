<?php
/**
 * Created by PhpStorm.
 * User: wang.haibo
 * Date: 2018/8/2
 * Time: 11:54
 */

namespace service;


abstract class Model
{

    /**
     * 获取 db 实例
     *
     * @author wang.haibo
     * @date   2018-08-02
     * @return \Medoo\Medoo
     * @throws \Exception
     */
    protected static function getDbInstance() {
        return Db::getInstance(static::getDb(), static::tableName());
    }

    /**
     * 表名
     *
     * @author wang.haibo
     * @date   2018-08-02
     */
    abstract static function tableName();

    /**
     * 获取数据库
     *
     * @author wang.haibo
     * @date   2018-08-02
     */
    abstract static function getDb();

    /**
     * 查询
     *
     * @param string $columns
     * @param array $_aryWhere
     * @throws \Exception
     * @return array
     */
    public static function findOne($columns = '*', array $_aryWhere = []) {
        $db = self::getDbInstance();
        return $db->get(static::tableName(), $columns, $_aryWhere);
    }

    /**
     * 查询
     *
     * @param array $_aryWhere
     * @throws \Exception
     * @return array
     */
    public static function findAll(array $_aryWhere = []) {
        $db = self::getDbInstance();
        return $db->select(static::tableName(), '*', $_aryWhere);
    }

    /**
     * 插入
     *
     * @author wang.haibo
     * @date   2018-08-03
     * @param  array $_aryDatas
     * @return bool
     * @throws \Exception
     */
    public static function insert(array $_aryDatas) {
        $db = self::getDbInstance();
        return $db->insert(static::tableName(), $_aryDatas);
    }

}