<?php
/*
* @desc: 重写FileLogger
* @author： coralme
* @date: 2024/5/1610:25
*/

namespace PrettyLog\Phal\Kernel;
use PhalApi\Exception\InternalServerErrorException;
use PhalApi\Logger\FileLogger as BasicFileLogger;
use PhalApi\Tool;
use PrettyLog\InterfaceLog;

class FileLogger extends BasicFileLogger implements InterfaceLog
{
    /**
     * @throws InternalServerErrorException
     */
    public function log($type, $msg, $data) {
        $this->init();
        $msgArr = array();
        //增加常规参数
        $msgArr['message'] = str_replace(PHP_EOL, '\n', $msg);
        $msgArr['datetime'] = date($this->dateFormat, time());
        $msgArr['level_name'] = strtolower($type);
        $msgArr['client_ip'] = Tool::getClientIp();
        $msgArr['channel'] = substr($this->filePrefix, 0, -1);
        $msgArr['context'] = $data;
        $requestId = \PrettyLog\Phal\Kernel\Tool::getRequestId();
        $msgArr['request_id'] = $requestId;
        //日志记录为json格式
        $content = json_encode($msgArr, 256);
        $content = $content . PHP_EOL;
        if ($this->debug) {
            // 调试时，显示创建，更友好的提示
            if (!is_writeable($this->logFile)) {
                throw new InternalServerErrorException(\PhalAPi\T('Failed to log into file, because permission denied: {path}', array('path' => Tool::getAbsolutePath($this->logFile))));
            }
            file_put_contents($this->logFile, $content, FILE_APPEND);
        } else {
            // 静默写入
            @file_put_contents($this->logFile, $content, FILE_APPEND);
        }
    }
}