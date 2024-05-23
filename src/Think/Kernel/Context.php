<?php
/*
* @desc: 全局上下文
* @author： coralme
* @date: 2024/5/21 9:59
*/

namespace PrettyLog\Think\Kernel;

use PrettyLog\Uuid;
use think\Container;

/**
 * @property string $requestId
 */
class Context
{
    public function __construct()
    {
        $request = Container::getInstance()->request;
        if (!empty($request->header('X-Request-Id'))) {
            $this->requestId = $request->header('X-Request-Id');
        } else {
            $this->requestId = Uuid::uuid4();
        }
    }
}