<?php
/**
 * Created by PhpStorm.
 * User: wang.haibo
 * Date: 2018/8/2
 * Time: 16:30
 */

namespace library\curl;



class Network
{

    public static function curlReq($url, $post = false, $data = array(), $timeout = 2, $CA = true) {
        $cacert = getcwd(). '/config/certificate/cacert.pem'; //CA根证书
        $SSL = substr($url, 0, 8) == "https://" ? true : false;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout-2);
        if ($SSL && $CA) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);   // 只信任CA颁布的证书
            curl_setopt($ch, CURLOPT_CAINFO, $cacert); // CA根证书（用来验证的网站证书是否是CA颁布）
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名，并且是否与提供的主机名匹配
        } else if ($SSL && !$CA) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 检查证书中是否设置域名
        }
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:')); //避免data数据过长问题
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            //curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); //data with URLEncode
        }

        $ret = curl_exec($ch);
        $rescode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //var_dump(curl_error($ch));  //查看报错信息

        curl_close($ch);
        return $ret;
    }

}