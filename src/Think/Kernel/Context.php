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
    public $requestId='';
}