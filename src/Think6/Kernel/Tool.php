<?php
/*
* @desc: 工具类
* @author： coralme
* @date: 2024/7/19 17:44
*/

namespace PrettyLog\Think6\Kernel;

use PrettyLog\LogTag;
use PrettyLog\Think6\Logger;
use think\App;
use think\event\AppInit;
use think\event\HttpEnd;

class Tool
{
    public static function getFullUrl(): string
    {
        $request = App::getInstance()->request;
        $schema = $request->scheme();
        $host = $request->host();
        $port = $request->port();
        $path = $request->server('REQUEST_URI');
        return $schema . '://' . $host . ($port == 80 ? '' : ':' . $port) . $path;
    }

    public static function getRequestId(): string
    {
        if (App::getInstance()->has('context')) {
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
    public static function HookHandler(App $app)
    {
        $app->bind('log', Log::class);
        $app->bind('Psr\Log\LoggerInterface', Log::class);
        $app->bind('context', Context::class);
        $app->event->listen(HttpEnd::class, function ($response) {
            $data = $response->getData();
            if (is_string($data)) {//view
                $code = $response->getCode();
                if ($code == 200) {
                    $data = [
                        'action' => 'view',
                        'path' => $data,
                        'data' => $response->getVars()
                    ];
                    Logger::AppResponse($data, true);
                } else {
                    $data = [
                        'action' => 'redirect',
                        'data' => substr($data, 0, 200)
                    ];
                    Logger::AppResponse($data);
                }
            } else {//json
                //think6框架在这里加request_id不行，需要在index.php里添加
//                $data['request_id'] = Tool::getRequestId();
                $response->data($data);
                Logger::AppResponse($data);
            }
        });
        $app->event->listen(AppInit::class, function () {
            Logger::AppRequest();
        });
    }
    public static function AppendRequestId(\think\Response &$response)
    {
        $data = $response->getData();
        $data['request_id'] = \PrettyLog\Think6\Kernel\Tool::getRequestId();
        $response->data($data);
    }
    public static function jsonMessage($msg, $levelType, $channel, $context)
    {
        //重写sql日志格式，便于后期在云日志添加慢查询sql监控
        if ($levelType == 'sql') {
            if (strpos($msg, 'CONNECT') === 0) {
                $context['sql'] = $msg;
                $context['cost'] = -1;//db连接不做统计
            } else {
                $costRegex = '/(?<=RunTime:).*(?=s)/';
                $sqlRegex = '/\[.*?\]/';
                $sql = preg_replace($sqlRegex, '', $msg);
                preg_match($costRegex, $msg, $matches);
                !empty($matches) && $cost = intval(floatval($matches[0]) * 1000);
                $context['sql'] = $sql;
                $context['cost'] = $cost ?? 0;
            }
            $msg = LogTag::SQL_MONITOR;
        }
        return json_encode([
            'message' => $msg,
            'datetime' => date('Y-m-d H:i:s'),
            'level_name' => $levelType,
            'channel' => $channel,
            'context' => $context,
            'request_id' => Tool::getRequestId(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}