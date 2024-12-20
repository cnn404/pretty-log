<?php
/*
* @desc: Phal工具类
* @author： coralme
* @date: 2024/5/159:59
*/

namespace PrettyLog\Phal\Kernel;

use PrettyLog\Uuid;
use function PhalApi\DI;

class Tool
{
    static public function getRequestId()
    {
        $requestId = DI()->request->getHeader('X-Request-Id');
        if (empty($requestId)) {
            $requestId = DI()->get('request_id');
        }
        return $requestId;
    }

    static public function setRequestId($requestId = null): string
    {
        $requestIdHeader = DI()->request->getHeader('X-Request-Id');
        if (!empty($requestIdHeader)) {
            $requestId = $requestIdHeader;
        }
        if (empty($requestId)) {
            $requestId = Uuid::uuid4();
        }
        DI()->set('request_id', $requestId);
        DI()->set('start_time', microtime(true));
        return $requestId;
    }
}