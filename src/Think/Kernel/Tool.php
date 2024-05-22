<?php
/*
* @desc: think框架日志工具类
* @author： coralme
* @date: 2024/5/17 19:52
*/

namespace PrettyLog\Think\Kernel;

use PrettyLog\LogTag;
use PrettyLog\Think\Logger;
use think\Container;

class Tool
{
    public static function getFullUrl(): string
    {
        $request = Container::getInstance()->request;
        $schema = $request->scheme();
        $host = $request->host();
        $port = $request->port();
        $path = $request->path();
        return $schema . '://' . $host . ($port == 80 ? '' : ':' . $port) . '/' . $path;
    }

    public static function getRequestId(): string
    {
        if (Container::getInstance()->has('context')) {
            return app('context')->requestId;
        }
        return '';
    }

    public static function setRequestId(): string
    {
        return app('context')->requestId;
    }

    /**
     * @desc: 监听框架事件
     * @param  $app
     * @return void
     * @author： coralme
     * @date: 2024/5/21 11:12
     */
    public static function HookHandler($app)
    {
        $app->bindTo('log', Log::class);
        $app->bindTo('think\LoggerInterface', Log::class);
        $app->bindTo('context', Context::class);
        $app->hook->add('app_init', function () {
            Logger::AppRequest();
        });
        $app->hook->add('app_end', function ($response) {
            $data = $response->getData();
            $data['request_id'] = Tool::getRequestId();
            $response->data($data);
            Logger::AppResponse($data);
        });
    }

    public static function jsonMessage($msg, $levelType, $channel, $context)
    {
        //重写sql日志格式，便于后期在云日志添加慢查询sql监控
        if (strpos($msg, '[ DB ]') === 0) {
            $context['sql'] = $msg;
            $msg = LogTag::SQL_MONITOR;
        } elseif (strpos($msg, '[ SQL ]') === 0) {
            $costRegex = '/(?<=RunTime:).*(?=s)/';
            $sqlRegex = '/\[.*?\]/';
            $sql = preg_replace($sqlRegex, '', $msg);
            preg_match($costRegex, $msg, $matches);
            !empty($matches) && $cost = intval(floatval($matches[0]) * 1000);
            $context['sql'] = $sql;
            $context['cost'] = $cost ?? 0;
            $msg = LogTag::SQL_MONITOR;
        }
        return json_encode([
            'message' => $msg,
            'datetime' => date('Y-m-d H:i:s'),
            'level_name' => $levelType,
            'channel' => $channel,
            'context' => $context,
            'request_id' => Tool::getRequestId(),
        ], 256);
    }
}