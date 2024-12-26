<?php
/*
* @desc: exception日志处理
* @author： coralme
* @date: 2024/5/22 8:42
*/

namespace PrettyLog\Phal\Kernel;

use \PhalApi\Error\ApiError as BasicApiError;
use PhalApi\Exception;
use PhalApi\Logger\FileLogger;
use PrettyLog\LogTag;
use PrettyLog\Phal\Logger;

class ApiError extends BasicApiError
{
    private $ignoreLogger = ['deprecated'];

    /**
     * 上报错误
     * @param array $context
     */
    protected function reportError($context)
    {
        if (in_array(strtolower($context['error']), $this->ignoreLogger)) {
            return;
        }
        $logger = Logger::getInstance('error');
        $logger->error(LogTag::APP_EXCEPTION, $context);
        $logger->writeLogs();
        $logger->setDefaultChannel();
    }
}