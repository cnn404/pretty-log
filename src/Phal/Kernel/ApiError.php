<?php
/*
* @desc: exception日志处理
* @author： coralme
* @date: 2024/5/22 8:42
*/

namespace PrettyLog\Phal\Kernel;

use \PhalApi\Error\ApiError as BasicApiError;
use PrettyLog\LogTag;

class ApiError extends BasicApiError
{
    protected function getLogger($type) {
        if (!isset($this->loggers[$type])) {
            $config = \PhalApi\DI()->config->get('sys.file_logger');
            $config['file_prefix'] = lcfirst($type);
            $this->loggers[$type] = FileLogger::create($config);
        }

        return $this->loggers[$type];
    }

    /**
     * 上报错误
     * @param array $context
     */
    protected function reportError($context) {
        $message = \PhalApi\T('{error} ({errno}): {errstr} in [File: {errfile}, Line: {errline}, Time: {time}]', $context);
        $this->getLogger($context['error'])->log($context['error'],LogTag::APP_EXCEPTION, ['error_message'=>$message]);
    }
}