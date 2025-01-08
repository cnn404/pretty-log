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
use PrettyLog\LogTag;
use function PhalApi\DI;

class FileLogger extends BasicFileLogger implements InterfaceLog
{
    private $fileSize = 300000000;//300MB
//    private $fileSize = 10000;//300MB
    //记录日志到内存
    public $logBuffer = [];

    public $once = false;
    //批量写日志最大数量
    const MAX_BATCH_SIZE = 20;
    private $currLogSize = 0;

    protected function init()
    {
        // 跨天时新建日记文件
        $curFileDate = date('Ymd', time());
        if ($this->fileDate == $curFileDate) {
            return;
        }
        $this->fileDate = $curFileDate;

        // 每月一个目录
        $folder = $this->folderPath();
        if (!file_exists($folder)) {
            if ($this->debug) {
                // 调试时，显示warning提示
                mkdir($folder, 0777, TRUE);
                chmod($folder, 0777);
            } else {
                // 静默创建
                @mkdir($folder, 0777, TRUE);
                @chmod($folder, 0777);
            }
        }

        // 每天一个文件
        $this->filePrefix = rtrim($this->filePrefix, '_');
        $filename = $this->filePrefix . '.log';
        $this->logFile = $folder . DIRECTORY_SEPARATOR . $filename;
        if (!file_exists($this->logFile)) {
            $this->newLogFile();
        } else {
            //检查文件大小
            if (filesize($this->logFile) > $this->fileSize) {
                $newFile = $folder . DIRECTORY_SEPARATOR . $this->filePrefix . '_' . date('YmdHis') . '.log';
                rename($this->logFile, $newFile);
                $this->newLogFile();
            }
        }
    }

    protected function setFilePrefix($filePrefix)
    {
        $this->filePrefix = $filePrefix;
    }

    private function newLogFile()
    {
        // 当没有权限时，touch会抛出(Permission denied)异常
        @touch($this->logFile);
        // touch失败时，chmod会抛出(No such file or directory)异常
        if (file_exists($this->logFile)) {
            chmod($this->logFile, 0777);
        }
    }

    /**
     * @throws InternalServerErrorException
     */
    public function log($type, $msg, $data)
    {
        if ($type == 'error' && $msg == DI()->request->getService()){
            $msg = LogTag::APP_EXCEPTION;
            $this->filePrefix = 'error';
        }
        $this->init();
        $msgArr = array();
        //增加常规参数
        $msgArr['message'] = str_replace(PHP_EOL, '\n', $msg);
        $msgArr['datetime'] = date($this->dateFormat, time());
        $msgArr['level_name'] = strtolower($type);
        $msgArr['client_ip'] = Tool::getClientIp();
        $msgArr['channel'] = $this->filePrefix;
        $msgArr['context'] = $data;
        $msgArr['request_id'] = \PrettyLog\Phal\Kernel\Tool::getRequestId();
        //日志记录为json格式
        $content = json_encode($msgArr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $content = $content . PHP_EOL;
        if (!$this->once && PHP_SAPI != 'cli') {
            $this->currLogSize++;
            $this->logBuffer[$this->filePrefix][] = $content;
            if ($this->currLogSize >= self::MAX_BATCH_SIZE) {
                $this->writeLogs();
            }
            return;
        }
        $logFile = $this->logFile;
        if ($this->debug) {
            // 调试时，显示创建，更友好的提示
            if (!is_writeable($logFile)) {
                throw new InternalServerErrorException(\PhalAPi\T('Failed to log into file, because permission denied: {path}', array('path' => Tool::getAbsolutePath($this->logFile))));
            }
            file_put_contents($logFile, $content, FILE_APPEND);
        } else {
            // 静默写入
//                error_log($content, 3, $logFile);
            @file_put_contents($logFile, $content, FILE_APPEND);
        }
    }

    public function writeLogs()
    {
        if (!empty($this->logBuffer)) {
            foreach ($this->logBuffer as $channel => $logs) {
                $logFile = $this->folderPath() . DIRECTORY_SEPARATOR . $channel . '.log';
                $content = implode('', $logs);
                @file_put_contents($logFile, $content, FILE_APPEND);
            }
            $this->logBuffer = [];
            $this->currLogSize = 0;
        }
    }

    private function folderPath()
    {
        return $this->logFolder
            . DIRECTORY_SEPARATOR . 'log'
            . DIRECTORY_SEPARATOR . substr($this->fileDate, 0, -2);
    }
}