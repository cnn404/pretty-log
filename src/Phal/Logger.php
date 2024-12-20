<?php

namespace PrettyLog\Phal;

use PrettyLog\AbstractLogger;
use PrettyLog\LogTag;
use PrettyLog\Phal\Kernel\FileLogger;
use PrettyLog\Phal\Kernel\Tool;
use function PhalApi\DI;

class Logger extends AbstractLogger
{
    public function __construct($channel = '', $logFolder = null)
    {
        self::defaultChannel($channel);
        defined('API_ROOT') || define('API_ROOT', dirname(__FILE__));
        if (empty($logFolder)) {
            $logFolder = API_ROOT . '/runtime';
        }
        $this->logger = FileLogger::create([
            'log_folder' => $logFolder,
            'file_prefix' => $channel
        ]);
        self::$instance = $this;
        register_shutdown_function([$this, 'shutdown']);
    }

    public function shutdown()
    {
        $this->writeLogs();
    }

    public static function getInstance($chanel = 'app', $logFolder = null): Logger
    {
        self::defaultChannel($chanel);
        !isset(self::$instance) && self::$instance = new self($chanel, $logFolder);
        self::$instance->logger->switchFilePrefix($chanel);
        return self::$instance;
    }

    public function setDefaultChannel()
    {
        $channel = self::defaultChannel();
        $this->logger->switchFilePrefix($channel);
    }

    private static function defaultChannel(&$channel = '')
    {
        if (empty($channel)) {
            if (PHP_SAPI == 'cli') {
                $channel = 'cli';
            } else {
                $channel = 'app';
            }
        }
        return $channel;
    }

    public function log($type, $msg, $data)
    {
        $sqlCheckRegex = '/\[.*?SQL\]/';
        //sql日志
        if (preg_match($sqlCheckRegex, $msg, $m0)) {
            $str = $m0[0];
            $data['sql'] = $msg;
            $costRegex = '/(?<=#\d\s-\s)\d*\.\d*(?=ms)/';
            if (preg_match($costRegex, $str, $m1)) {
                $data['cost'] = floatval($m1[0]);
            }
            $msg = LogTag::SQL_MONITOR;
        }
        return $this->logger->log($type, $msg, $data);
    }

    /**
     * @desc: 记录所有的sql日志
     * @param $data
     * @return mixed
     * @author： coralme
     * @date: 2024/5/17 15:55
     */
    public static function Sql($data, $once = false)
    {
        return self::getInstance()->info(LogTag::SQL_MONITOR, $data);
    }

    public function info($message, $data = [], $once = false)
    {
        $this->logger->once = $once;
        return $this->logger->info($message, $data);
    }

    public function error($message, $data = [], $once = false)
    {
        $this->logger->once = $once;
        return $this->logger->error($message, $data);
    }

    public function debug($message, $data = [], $once = false)
    {
        $this->logger->once = $once;
        return $this->logger->debug($message, $data);
    }

    /**
     * @desc: web app入参记录日志,第一条日志
     * @param array $reqContext
     * @return NULL
     * @author： coralme
     * @date: 2024/5/159:12
     */
    public static function AppRequest(array $reqContext = [])
    {
        if (PHP_SAPI == 'cli') {
            return false;
        }
        Tool::setRequestId();
        $request = \PhalApi\DI()->request;
        $data = [
            'method' => $request->parseMethod(),
            'url' => $request->getFullUrl(),
            'payload' => $request->parseBody(),
        ];
        $data = array_merge($data, $reqContext);
        return self::getInstance()->info(LogTag::REQUEST, $data);
    }

    /**
     * @desc: web app 出参记录日志
     * @param array $respContext
     * @return NULL
     * @author： coralme
     * @date: 2024/5/159:12
     */
    public static function AppResponse(array $respContext = [], bool $isView = false)
    {
        $request = \PhalApi\DI()->request;
        $data = [
            'method' => $request->parseMethod(),
            'url' => $request->getFullUrl(),
            'payload' => $request->parseBody(),
        ];
        $respContext['extend'] = $data;
        $ret = self::getInstance()->info(LogTag::RESPONSE, $respContext);
        self::getInstance()->writeLogs();
        return $ret;
    }

    public function writeLogs()
    {
        $this->logger->writeLogs();
    }

    /**
     * @desc: app调用外部接口入参记录日志
     * @param array $reqContext
     * @return NULL
     * @author： coralme
     * @date: 2024/5/159:13
     */
    public static function ServiceRequest(array $reqContext = [], $once = false)
    {
        return self::getInstance()->info(LogTag::SERVICE_REQUEST, $reqContext, $once);
    }

    /**
     * @desc: app调用外部接口出参记录日志
     * @param array $respContext
     * @return NULL
     * @author： coralme
     * @date: 2024/5/159:13
     */
    public static function ServiceResponse(array $respContext = [], $once = false)
    {
        return self::getInstance()->info(LogTag::SERVICE_RESPONSE, $respContext, $once);
    }
}