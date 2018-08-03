<?php
/**
 * Created by PhpStorm.
 * User: wang.haibo
 * Date: 2018/8/2
 * Time: 16:14
 */

namespace library\wechat;


use library\curl\Network;
use library\utils\RedisUtil;
use redis\Redis;
use service\Bnw;

class WeCahtApiTool
{

    /**
     * 获取 微信 Access Token
     *
     * @author wang.haibo
     * @date   2018-08-02
     * @return bool | string
     */
    public static function getAccessToken() {
        // 是否有缓存的 access token
        $strKey = RedisUtil::getWechatAccessTokenKey();
        $aryToken = Redis::getStringCache($strKey);
        if ($aryToken) {
            return $aryToken['access_token'];
        }

        // 获取新的 access token
        $strUrl = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.Bnw::$app->config['params']['wecaht']['appid'].'&secret='.Bnw::$app->config['params']['wecaht']['secret'];
        $access_request = Network::curlReq($strUrl, false);
        $access_result = json_decode($access_request, true);
        if ($access_result) {
            Redis::setStringCache($strKey, $access_result, $access_result['expires_in']);
            return $access_result['access_token'];
        }
        return false;
    }

    /**
     * 微信小店发货
     *
     * @author wang.haibo
     * @date   2018-08-03
     * @return bool | array
     */
    public static function sendGoods() {

    }

    /**
     * 发送微信消息
     *
     * @param  string $_strMessage 消息内容
     * @param  string $_strOpenid 用户id
     * @param  string $_strAccessToken
     * @author wang.haibo
     * @date   2018-08-03
     * @return bool | array
     */
    public static function sendMsg($_strMessage, $_strOpenid, $_strAccessToken = '') {
        !$_strAccessToken ? $_strAccessToken = self::getAccessToken() : null;
        $arySendData = [
            'touser' => $_strOpenid,
            'msgtype' => 'text',
            'text' => [
                'content' => $_strMessage,
            ],
        ];
        $send_order_request = Network::curlReq('https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$_strAccessToken, true, json_encode($arySendData, JSON_UNESCAPED_UNICODE));
        return json_decode($send_order_request, true);
    }
}