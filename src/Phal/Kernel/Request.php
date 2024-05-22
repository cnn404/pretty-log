<?php
/*
* @desc: request添加方法
* @author： coralme
* @date: 2024/5/1514:33
*/

namespace PrettyLog\Phal\Kernel;

class Request extends \PhalApi\Request
{

    public function getFullUrl(): string
    {
        // 获取协议（HTTP 或 HTTPS）
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

        // 获取主机名（域名）
        $host = $_SERVER['HTTP_HOST'];

        // 获取请求的 URI
        $uri = $_SERVER['REQUEST_URI'];

        // 拼接完整的 URL
        return $protocol . $host . $uri;
    }

    public function parseBody()
    {
        if (empty($_FILES)) {
            $rawData = file_get_contents('php://input');
            $payload = json_decode($rawData, true);
            if (empty($payload)) {
                $payload = $rawData;
            }
        } else {
            $payload = 'file';
        }
        return $payload;
    }

    public function parsePath()
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    public function parseQuery()
    {
        return $_SERVER['QUERY_STRING'];
    }

    public function parseMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function getHeaders()
    {
        return parent::getAllHeaders();
    }

    public function getClientIP(): string
    {
        return \PhalApi\Tool::getClientIp();

    }

}