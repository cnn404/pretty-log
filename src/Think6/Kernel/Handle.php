<?php
/*
* @desc: 错误处理类
* @author： coralme
* @date: 2024/7/26 14:54
*/

namespace PrettyLog\Think6\Kernel;

use think\exception\Handle as BasicHandle;
use Throwable;
use PrettyLog\Think6\Logger;
use PrettyLog\LogTag;

class Handle extends BasicHandle
{
    public function report(Throwable $exception): void
    {
        if (!$this->isIgnoreReport($exception)) {
            // 收集异常数据
            if ($this->app->isDebug()) {
                $data = [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'message' => $this->getMessage($exception),
                    'code' => $this->getCode($exception),
                ];
                $log = "[{$data['code']}]{$data['message']}[{$data['file']}:{$data['line']}]";
            } else {
                $data = [
                    'code' => $this->getCode($exception),
                    'message' => $this->getMessage($exception),
                ];
                $log = "[{$data['code']}]{$data['message']}";
            }

            if ($this->app->config->get('log.record_trace')) {
                $log .= PHP_EOL . $exception->getTraceAsString();
            }
            Logger::getInstance()->error(LogTag::APP_EXCEPTION, ['error_message' => $log]);
        }
    }
}