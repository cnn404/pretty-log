<?php
/*
* @desc: think框架日志工具类
* @author： coralme
* @date: 2024/5/17 19:52
*/

namespace PrettyLog\Think\Kernel;

use PrettyLog\LogTag;
use PrettyLog\Think\Logger;
use PrettyLog\Uuid;
use think\Container;

class Tool
{
    public static function getFullUrl(): string
    {
        $request = Container::getInstance()->request;
        $schema = $request->scheme();
        $host = $request->host();
        $port = $request->port();
        $url = $request->url();
        return $schema . '://' . $host . ($port == 80 ? '' : ':' . $port) . $url;
    }

    public static function getPayload()
    {
        $request = Container::getInstance()->request;
        if (empty($_FILES)) {
            $payload = json_decode($request->getContent(), true);
            if (empty($payload)) {
                $payload = $request->getContent();
            }
        } else {
            $payload = 'file';
        }
        return $payload;
    }
    public static function getRequestId(): string
    {
        if (Container::getInstance()->has('context')) {
            return app('context')->requestId;
        }
        return '';
    }

    public static function setRequestId($requestId = null): string
    {
        $request = Container::getInstance()->request;
        if (!empty($request->header('X-Request-Id'))) {
            $requestId = $request->header('X-Request-Id');
        }
        if (empty($requestId)) {
            $requestId = Uuid::uuid4();
        }
        if (Container::getInstance()->has('context')) {
            app('context')->requestId = $requestId;
        }
        return $requestId;
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
        $app->hook->add('app_end', function (\think\Response $response) {
            $data = $response->getData();
            if (is_string($data)) {//view
                $code = $response->getCode();
                if ($code == 200) {
                    $data = [
                        'action' => 'view',
                        'data' => ''
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
                $data['request_id'] = Tool::getRequestId();
                $response->data($data);
                Logger::AppResponse($data);
            }
        });
    }

    public static function jsonMessage($msg, $levelType, $channel, $context)
    {
        //重写sql日志格式，便于后期在云日志添加慢查询sql监控
        if ($levelType == 'sql') {
            if (strpos($msg, '[ DB ]') === 0) {
                $context['sql'] = $msg;
                $context['cost'] = -1;//db连接不做统计
            } elseif (strpos($msg, '[ SQL ]') === 0) {
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
            'message'    => $msg,
            'datetime'   => date('Y-m-d H:i:s'),
            'level_name' => $levelType,
            'client_ip'  => Container::getInstance()->request->ip(),
            'channel'    => $channel,
            'context'    => $context,
            'request_id' => Tool::getRequestId(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}