<?php
/**
 * Created by PhpStorm.
 * User: wang.haibo
 * Date: 2018/8/2
 * Time: 15:49
 */

namespace models;


use service\Bnw;
use service\Model;

class Tskey extends Model
{

    public static function getDb()
    {
        // TODO: Implement getDb() method.
        return Bnw::$app->config['db']['db'];
    }

    public static function tableName()
    {
        // TODO: Implement tableName() method.
        return 'tskey';
    }

}