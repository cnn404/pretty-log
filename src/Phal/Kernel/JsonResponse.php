<?php
/*
* @desc: 接口返回添加日志
* @author： coralme
* @date: 2024/5/15 9:52
*/

namespace PrettyLog\Phal\Kernel;

use PhalApi\Response\JsonResponse as PhalResponse;
use PrettyLog\Phal\Logger;

class JsonResponse extends PhalResponse
{
    public function getResult(): array
    {
        $rs = parent::getResult();
        $rs['request_id'] = Tool::getRequestId();
        return $rs;
    }

    public function output()
    {
        $startTime = \PhalApi\DI()->get('start_time');
        if (empty($startTime)) {
            $startTime = microtime(true);
        }
        Logger::AppResponse(array_merge(
            $this->getResult(),
            ['cost' => intval(1000 * (microtime(true) - $startTime))]
        ));
        parent::output();
    }
}