<?php
/**
 * Created by PhpStorm.
 * User: wang.haibo
 * Date: 2018/8/1
 * Time: 17:59
 */

namespace service;


/**
 * @author Wang Haibo <whb@play4u.cn>
 * @date   2018-08-02
 */
class App
{

    /** config 配置项 */
    public $config;

    /**
     * @var $logger \service\Logger
     */
    public $logger;

    /**
     * App constructor.
     * @param array $_aryConfig
     */
    public function __construct($_aryConfig)
    {
        Bnw::$app = $this;
        $this->config = $_aryConfig;
        // 加载组件
        $this->loadComponent();
    }

    /**
     * 启动服务
     *
     * @author wang.haibo
     * @date   2018-08-01
     */
    public function run() {
        // 路由
        $this->route();
    }

    /**
     * 路由
     *
     * @author wang.haibo
     * @date   2018-08-01
     */
    private function route() {
        // 路由检查
        if (count($_SERVER['argv']) != 2) {
            exit('route errors'.PHP_EOL);
        }
        $strRoute = $_SERVER['argv'][1];
        $aryRoute = explode('/', $strRoute);
        $strClassName = '\\commands\\'.$aryRoute[0];
        $strMethod = isset($aryRoute[1]) ? $aryRoute[1] : 'index';
        $boolClassExists = class_exists($strClassName);
        if (!$boolClassExists) {
            exit('route errors: '. $strClassName . ' class not found!'.PHP_EOL);
        }
        $objClass = new $strClassName;
        $boolMethodExists = method_exists($objClass, $strMethod);
        if (!$boolMethodExists) {
            exit('route errors: '. $strClassName . '\\'. $strMethod . ' method not found!'.PHP_EOL);
        }

        // 执行方法
        $objClass->$strMethod();
    }

    /**
     * 加载组件
     *
     * @author wang.haibo
     * @date   2018-08-02
     */
    private function loadComponent() {
        // 加载 logger 组件
        $this->logger = new \service\Logger();
    }

}