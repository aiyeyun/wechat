<?php
/**
 * Created by PhpStorm.
 * User: wang.haibo
 * Date: 2018/8/1
 * Time: 17:39
 */

namespace commands;


use library\curl\Network;
use library\utils\WechatShopOrderUtil;
use library\wechat\WeCahtApiTool;
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
     */
    private function sendGoods(array $_aryOrderInfo) {
        Bnw::$app->logger->info("微信订单号: {$_aryOrderInfo['order_id']} 正在准备发货");
        // 获取购买商品详情
        $aryGoodsDetails = WechatShopOrderUtil::getGoodsDetails($_aryOrderInfo['product_price']);
        // 购买的测试商品
        if ($aryGoodsDetails['order_type'] === 0) {
            // 发送
            WeCahtApiTool::sendMsg('感谢您购买测试商品！', $_aryOrderInfo['buyer_openid']);
        }
    }


}