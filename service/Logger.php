<?php
/**
 * Created by PhpStorm.
 * User: wang.haibo
 * Date: 2018/8/2
 * Time: 11:15
 */

namespace service;


use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryUsageProcessor;

/**
 * @author Wang Haibo <whb@play4u.cn>
 * @date   2018-08-02
 */
class Logger
{

    /** @var $logger \Monolog\Logger */
    private $logger;

    public function __construct()
    {
        // runtime 文件夹
        $strRuntime = dirname(__DIR__).'/runtime';
        if (!is_dir($strRuntime)) {
            mkdir($strRuntime, 0777, true);
        }

        $this->logger = new \Monolog\Logger('帮你玩');
        $date = date('Y-m-d');
        $streamHandler = new StreamHandler($strRuntime . '/logs/' . $date . '.log');
        // 日志格式 按字符串格式记录
        $streamHandler->setFormatter(new LineFormatter());
        $this->logger->pushHandler($streamHandler);
        // 增加内存使用记录
        $this->logger->pushProcessor(new MemoryUsageProcessor());
    }

    /**
     * info
     *
     * @author wang.haibo
     * @date   2018-08-02
     * @param  string $_strMessage
     * @param  array  $_aryContext
     */
    public function info($_strMessage, array $_aryContext = []) {
        $this->logger->addInfo($_strMessage, $_aryContext);
    }

    /**
     * error
     *
     * @author wang.haibo
     * @date   2018-08-02
     * @param  string $_strMessage
     * @param  array  $_aryContext
     */
    public function error($_strMessage, array $_aryContext = []) {
        $this->logger->addError($_strMessage, $_aryContext);
    }

    /**
     * emergency
     *
     * @author wang.haibo
     * @date   2018-08-02
     * @param  string $_strMessage
     * @param  array  $_aryContext
     */
    public function emergency($_strMessage, array $_aryContext = []) {
        // 记录致命错误详情
        $this->logger->pushProcessor(new IntrospectionProcessor());
        $this->logger->addEmergency($_strMessage, $_aryContext);
    }

}