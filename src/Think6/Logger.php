<?php
/*
* @desc: 业务调用日志类
* @author： coralme
* @date: 2024/7/19 17:43
*/

namespace PrettyLog\Think6;

use PrettyLog\AbstractLogger;
use PrettyLog\LogTag;
use think\App;
use PrettyLog\Think6\Kernel\Tool;
/**
 * @method error(mixed $message, array $data = []) static 记录error信息
 * @method info(mixed $message, array $data = []) static 记录info信息
 * @method debug(mixed $message, array $data = []) static 记录debug信息
 */
class Logger extends AbstractLogger
{

    public function __construct($channel = 'app')
    {
        $this->channel = $channel;
        $this->logger = app('log');
    }

    public static function getInstance($channel = 'app'): Logger
    {
        !isset(self::$instance) && self::$instance = new self($channel);
        self::$instance->channel = $channel;
        return self::$instance;
    }


    /**
     * @desc: 记录所有的sql日志
     * @param $data
     * @return mixed
     * @author： coralme
     * @date: 2024/5/17 15:55
     */
    public static function Sql($data)
    {
        return self::getInstance()->info(LogTag::SQL_MONITOR, $data);
    }

    /**
     * @desc: web app入参记录日志,第一条日志
     * @param array $reqContext 请求上下文，可不填
     * @return NULL
     * @author： coralme
     * @date: 2024/5/159:12
     */
    public static function AppRequest(array $reqData = [])
    {

        if (PHP_SAPI == 'cli') {
            return false;
        }
        
        $request = App::getInstance()->request;

        Tool::setRequestId();

        if (empty($_FILES)) {
            $payload = json_decode($request->getContent(), true);
            if (empty($payload)) {
                $payload = $request->getContent();
            }
        } else {
            $payload = 'file';
        }

        $data = [
            'method' => $request->method(),
            'url' => Tool::getFullUrl(),
            'query' => $request->query(),
            'payload' => $payload,
//            'header' => $request->header(),
        ];

        $data = array_merge($data, $reqData);
        return self::getInstance()->info(LogTag::REQUEST, $data);
    }

    /**
     * @desc: web app 出参记录日志
     * @param array $respContext
     * @return NULL
     * @author： coralme
     * @date: 2024/5/159:12
     */
    public static function AppResponse(array $respData = [], $isView = false)
    {
        $beginTime = App::getInstance()->getBeginTime();
        $respData['cost'] = intval(1000 * (microtime(true) - $beginTime));
        $msg = LogTag::RESPONSE;
        $isView && $msg = LogTag::VIEW_RESPONSE;
        return self::getInstance()->info($msg, $respData);
    }

    /**
     * @desc: app调用外部接口入参记录日志
     * @param array $reqContext
     * @return NULL
     * @author： coralme
     * @date: 2024/5/159:13
     */
    public static function ServiceRequest(array $reqData = [])
    {
        return self::getInstance()->info(LogTag::SERVICE_REQUEST, $reqData);
    }

    /**
     * @desc: app调用外部接口出参记录日志
     * @param array $respContext
     * @return NULL
     * @author： coralme
     * @date: 2024/5/159:13
     */
    public static function ServiceResponse(array $respData = [])
    {
        return self::getInstance()->info(LogTag::SERVICE_RESPONSE, $respData);
    }

    public function __call($name, $arguments)
    {
        if (in_array($name, ['info', 'error', 'debug'])) {
            $message = $arguments[0];
            $data = $arguments[1] ?? [];
            $func = $name . '_' . $this->channel;
            
            $this->logger->$func($message, $data);
        } else {
            throw new \Exception("$name method not exist.");
        }
    }
}