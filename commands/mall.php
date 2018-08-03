<?php
/**
 * Created by PhpStorm.
 * User: wang.haibo
 * Date: 2018/8/1
 * Time: 17:39
 */

namespace commands;


use library\curl\Network;
use library\utils\RedisUtil;
use library\utils\TskeyUtil;
use library\utils\WechatShopOrderUtil;
use library\wechat\WeCahtApiTool;
use models\Tskey;
use models\User;
use models\WechatShopOrder;
use service\Bnw;

/**
 * 微信小店
 *
 * Class Mall
 * @package commands
 */
class mall
{

    public function index() {
        Bnw::$app->logger->info('------------------------- 微信小店订单 轮询 Start -------------------------');
        $this->execute();
        Bnw::$app->logger->info('------------------------- 微信小店订单 轮询 End -------------------------');
    }

    /**
     * 执行任务
     *
     * @author wang.haibo
     * @date   2018-08-03
     */
    private function execute() {
        try{
            // 获取当天的购买订单
            $aryOrder = $this->getOrders();
            // 订单获取到了 准备数据入库
            $this->dataJoinDb($aryOrder);
        }catch (\Exception $exception) {
            Bnw::$app->logger->emergency($exception->getMessage());
        }
    }

    /**
     * 获取当天的购买订单
     *
     * @author wang.haibo
     * @date   2018-08-03
     * @return array | bool
     */
    private function getOrders() {
        // 微信 access token
        $strWecahtAccessToken = WeCahtApiTool::getAccessToken();
        // 查询订单
        $strQueryOrderUrl = 'https://api.weixin.qq.com/merchant/order/getbyfilter?access_token='.$strWecahtAccessToken;
        // 订单状态(不带该字段-全部状态, 2-待发货, 3-已发货, 5-已完成, 8-维权中, )
        $strPostData = json_encode([
            'status' => 2,
            'begintime' => strtotime(date('Y-m-d')),
            'endtime' => strtotime(date('Y-m-d',strtotime('+1 day'))),
        ]);
        $reulst = Network::curlReq($strQueryOrderUrl, true, $strPostData);
        $aryReulst = json_decode($reulst, true);
        if ($aryReulst && isset($aryReulst['order_list']) && is_array($aryReulst['order_list'])) {
            return $aryReulst['order_list'];
        }
        return false;
    }

    /**
     * 订单数据入库
     *
     * @author wang.haibo
     * @date   2018-08-03
     * @param  array $_aryOrder
     * @throws \Exception
     */
    private function dataJoinDb($_aryOrder) {
        foreach ($_aryOrder as $aryOrderInfo) {
            // redis 加锁
            $strKey = RedisUtil::getWechatOrderIdLockKey($aryOrderInfo['order_id']);
            $lock = \redis\Redis::getStringCache($strKey);
            if ($lock) {
                continue;
            }
            // 订单号加锁 防止多进程 抢夺资源
            \redis\Redis::setStringCache($strKey, $strKey, 86400*10);

            // 检查此订单是否已经入库
            $aryWechatOrder = WechatShopOrder::findOne('id', [ 'order_id[=]' => $aryOrderInfo['order_id'] ]);
            // 此订单已经入库
            if ($aryWechatOrder) {
                continue;
            }

            // 订单入库
            $this->joinDb($aryOrderInfo);
            // 订单发货
            $this->sendGoods($aryOrderInfo);
        }
    }

    /**
     * 订单数据入库
     *
     * @param array $_aryOrderInfo
     * @throws \Exception
     */
    private function joinDb(array $_aryOrderInfo) {
        Bnw::$app->logger->info("微信订单号: {$_aryOrderInfo['order_id']} 准备入库");
        // 订单入库
        $boolInsert = WechatShopOrder::insert([
            'order_id' => $_aryOrderInfo['order_id'],
            'order_status' => isset($_aryOrderInfo['order_status']) ? $_aryOrderInfo['order_status'] : null,
            'order_total_price' => isset($_aryOrderInfo['order_total_price']) ? $_aryOrderInfo['order_total_price'] : null,
            'order_create_time' => isset($_aryOrderInfo['order_create_time']) ? $_aryOrderInfo['order_create_time'] : null,
            'order_express_price' => isset($_aryOrderInfo['order_express_price']) ? $_aryOrderInfo['order_express_price'] : null,
            'buyer_openid' => isset($_aryOrderInfo['buyer_openid']) ? $_aryOrderInfo['buyer_openid'] : null,
            'buyer_nick' => isset($_aryOrderInfo['buyer_nick']) ? $_aryOrderInfo['buyer_nick'] : null,
            'product_id' => isset($_aryOrderInfo['product_id']) ? $_aryOrderInfo['product_id'] : null,
            'product_name' => isset($_aryOrderInfo['product_name']) ? $_aryOrderInfo['product_name'] : null,
            'product_price' => isset($_aryOrderInfo['product_price']) ? $_aryOrderInfo['product_price'] : null,
            'product_sku' => isset($_aryOrderInfo['product_sku']) ? $_aryOrderInfo['product_sku'] : null,
            'product_count' => isset($_aryOrderInfo['product_count']) ? $_aryOrderInfo['product_count'] : null,
            'status' => WechatShopOrderUtil::STATUS_WAIT,
            'create_at' => date('Y-m-d H:i:s'),
            'update_at' => date('Y-m-d H:i:s'),
        ]);
        if (!$boolInsert) {
            throw new \Exception('微信订单号: '.$_aryOrderInfo['order_id']. ' 数据入库失败');
        }
        Bnw::$app->logger->info("微信订单号: {$_aryOrderInfo['order_id']} 数据入库成功");
    }

    /**
     * 发货
     *
     * @param  array $_aryOrderInfo
     * @author wang.haibo
     * @date   2018-08-03
     * @throws \Exception
     */
    private function sendGoods(array $_aryOrderInfo) {
        Bnw::$app->logger->info("微信订单号: {$_aryOrderInfo['order_id']} 正在准备发货");
        // 获取购买商品详情
        $aryGoodsDetails = WechatShopOrderUtil::getGoodsDetails($_aryOrderInfo['product_price']);
        // 购买的测试商品
        if ($aryGoodsDetails['order_type'] === 0) {
            // 微信小店发货
            WeCahtApiTool::sendGoods($_aryOrderInfo['order_id']);
            // 微信小店发货成功 订单状态更新
            $this->sendGoodsSuccess($_aryOrderInfo);
            // 发送 微信客服消息
            WeCahtApiTool::sendMsg('感谢您购买测试商品！', $_aryOrderInfo['buyer_openid']);
            return;
        }

        // 初始化变量 触动授权码
        $strKeycodes = '';

        // 订单是否已发货
        $aryTskey = Tskey::findAll('keycode', [ 'tradeno' => $_aryOrderInfo['buyer_openid'] ]);
        if ($aryTskey) {
            Bnw::$app->logger->info("微信订单号: {$_aryOrderInfo['order_id']} 此订单已经发货");
            foreach ($aryTskey as &$strKeycode) {
                $strKeycode = trim($strKeycode);
                $strKeycodes .= $aryGoodsDetails['order_nick'].' '.$strKeycode."\n";
            }

            // 发送 微信客服消息
            // 构造返回消息
            $strMessage = $_aryOrderInfo['receiver_name']."，您好。\n";
            $strMessage .= "您的订单已完成支付，订单号：".(string)$_aryOrderInfo['order_id']." 。\n";
            $strMessage .= "感谢您的购买与支持！\n";
            $strMessage .= "授权码列表：\n".$strKeycodes;
            WeCahtApiTool::sendMsg($strMessage, $_aryOrderInfo['buyer_openid']);
            return;
        }

        Bnw::$app->logger->info("微信订单号: {$_aryOrderInfo['order_id']} 订单未发货,现开始发货");
        // 提取触动授权码
        $aryTskeyList = Tskey::findAll(
            'keycode',
            [
                'vaildday' => $aryGoodsDetails['order_type'],
                'status'   => TskeyUtil::STATUS_UNSHELF ,
                // 提取 多少个授权码
                'LIMIT' => $_aryOrderInfo['product_count'],
            ]
        );

        // 库存不足 发货失败
        if (!$aryTskeyList || count($aryTskeyList) != $_aryOrderInfo['product_count']) {
            return $this->sendGoodsFail($_aryOrderInfo, $aryGoodsDetails['order_nick'].' 库存不足');
        }

        Bnw::$app->logger->info("微信订单号: {$_aryOrderInfo['order_id']} 正在提取触动授权码");
        // 准备发货
        foreach ($aryTskeyList as &$strKey) {
            $strKey = trim($strKey);
            $strKeycodes .= $aryGoodsDetails['order_nick'].' '.$strKey."\n";
        }
        Bnw::$app->logger->info("微信订单号: {$_aryOrderInfo['order_id']} 触动授权码提取完毕");
        // 发货
        $updateTskey = Tskey::update(
            [
                'status' => TskeyUtil::STATUS_UNUSED,
                'tradeno' => $_aryOrderInfo['order_id'],
                'buyer_id' => $_aryOrderInfo['buyer_openid'],
                'buy_time' => time(),
            ],
            [ 'keycode' => $aryTskeyList ]
        );

        // 发货失败
        if (!$updateTskey) {
            return $this->sendGoodsFail($_aryOrderInfo, 'tskey 表发货失败');
        }

        // 微信小店接口发货
        Bnw::$app->logger->info("微信订单号: {$_aryOrderInfo['order_id']} 微信小店接口发货");
        WeCahtApiTool::sendGoods($_aryOrderInfo['order_id']);

        // 发送 微信客服消息
        // 构造返回消息
        Bnw::$app->logger->info("微信订单号: {$_aryOrderInfo['order_id']} 发送微信客服消息");
        $strMessage = $_aryOrderInfo['receiver_name']."，您好。\n";
        $strMessage .= "您的订单已完成支付，订单号：".(string)$_aryOrderInfo['order_id']." 。\n";
        $strMessage .= "感谢您的购买与支持！\n";
        $strMessage .= "授权码列表：\n".$strKeycodes;
        WeCahtApiTool::sendMsg($strMessage, $_aryOrderInfo['buyer_openid']);
    }

    /**
     * 发货失败 订单 更新
     *
     * @param  array $_aryOrderInfo
     * @param  string $_strMessage
     * @author wang.haibo
     * @date   2018-08-03
     * @throws \Exception
     */
    private function sendGoodsFail(array $_aryOrderInfo, $_strMessage) {
        // 订单标记失败 并做好 笔记
        $updateShopOrder = WechatShopOrder::update(
            [
                'status' => WechatShopOrderUtil::STATUS_FAIL,
                'note'   => $_strMessage,
                'update_at' => date('Y-m-d H:i:s')
            ],
            [ 'order_id[=]' => $_aryOrderInfo['order_id'] ]
        );
        if (!$updateShopOrder) {
            throw new \Exception('微信订单号: '. $_aryOrderInfo['order_id'].' '. $_strMessage . ' 订单状态更新失败');
        }
        Bnw::$app->logger->info("微信订单号: {$_aryOrderInfo['order_id']} $_strMessage 发货失败");
    }

    /**
     * 发货成功 订单 更新
     *
     * @author wang.haibo
     * @date   2018-08-03
     * @param  array $_aryOrderInfo
     * @throws \Exception
     */
    private function sendGoodsSuccess(array $_aryOrderInfo) {
        // 订单标记失败 并做好 笔记
        $updateShopOrder = WechatShopOrder::update(
            [
                'status' => WechatShopOrderUtil::STATUS_SUCCESS,
                'send_at' => date('Y-m-d H:i:s'),
                'update_at' => date('Y-m-d H:i:s'),
            ],
            [ 'order_id[=]' => $_aryOrderInfo['order_id'] ]
        );
        if (!$updateShopOrder)
        {
            throw new \Exception('微信订单号: '. $_aryOrderInfo['order_id']. ' 微信小店接口发货成功 , 订单状态更新失败');
        }
        Bnw::$app->logger->info("微信订单号: {$_aryOrderInfo['order_id']} 微信小店接口 发货成功");
    }

}