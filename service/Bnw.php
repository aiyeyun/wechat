<?php
/**
 * @link http://www.touchsprite.com/
 * @copyright Copyright (c) 2008 北京帮你玩科技有限公司
 */


namespace service;


/**
 * @author Wang Haibo <whb@play4u.cn>
 * @date   2018-08-01
 */
class Bnw
{

    /** @var $app App */
    public static $app;

    /**
     * 版本号
     *
     * @author wang.haibo
     * @date   2018-08-02
     * @return string
     */
    public static function version() {
        return '1.0.0';
    }

    /**
     * 作者
     *
     * @author wang.haibo
     * @date   2018-08-02
     * @return array
     */
    public static function author() {
        return [
            'author' => 'wang.haibo',
            'email'  => 'whb@play4u.cn',
        ];
    }

}