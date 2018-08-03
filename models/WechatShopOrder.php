<?php
/**
 * Created by PhpStorm.
 * User: wang.haibo
 * Date: 2018/8/3
 * Time: 12:40
 */

namespace models;


use service\Bnw;
use service\Model;

class WechatShopOrder extends Model
{

    public static function getDb()
    {
        // TODO: Implement getDb() method.
        return Bnw::$app->config['db']['db'];
    }

    public static function tableName()
    {
        // TODO: Implement tableName() method.
        return 'ts_wechat_shop_order';
    }

}