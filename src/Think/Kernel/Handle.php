<?php
/*
* @desc: 文件描述header
* @author： coralme
* @date: 2024/5/22 11:47
*/

namespace PrettyLog\Think\Kernel;

use Exception;
use think\Container;
use think\exception\Handle as BasicHandle;
use \PrettyLog\Think\Logger;
use \PrettyLog\LogTag;

class Handle extends BasicHandle
{
    public function report(Exception $exception)
    {
        if (!$this->isIgnoreReport($exception)) {
            $data = [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'message' => $this->getMessage($exception),
                'code' => $this->getCode($exception),
                'trace'=>$exception->getTraceAsString()
            ];
            Logger::getInstance()->error(LogTag::APP_EXCEPTION, $data);
        }
    }
}