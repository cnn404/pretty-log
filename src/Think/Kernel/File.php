<?php
/*
* @desc: 自定义driver File
* @author： coralme
* @date: 2024/5/17 8:58
*/

namespace PrettyLog\Think\Kernel;

use think\log\driver\File as BasicFile;

class File extends BasicFile
{

    private string $channel = 'app';

    private $fileSize = 300000000;//300MB
//    private $fileSize = 10000;//300MB


    protected function getMasterLogFile()
    {
        if ($this->config['max_files']) {
            $files = glob($this->config['path'] . '*.log');

            try {
                if (count($files) > $this->config['max_files']) {
                    unlink($files[0]);
                }
            } catch (\Exception $e) {
            }
        }

        $cli = PHP_SAPI == 'cli' ? '_cli' : '';

        if ($this->config['single']) {
            $name = is_string($this->config['single']) ? $this->config['single'] : 'single';
            $destination = $this->config['path'] . $name . $cli . '.log';
        } else {
            $destination = $this->config['path'] . date('Ym').DIRECTORY_SEPARATOR . $this->fileName($this->channel);
        }
        return $destination;
    }
    protected function checkLogSize($destination)
    {

        if (is_file($destination) && floor($this->fileSize) <= filesize($destination)) {
            try {
                $info =  pathinfo($destination);
                $newDestination = $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'] . '_' . date('YmdHis') . '.log';
                rename($destination, $newDestination);
            } catch (\Exception $e) {
            }
        }
    }
    public function fileName($channel=''): string
    {
        self::defaultChannel($channel);
        return $channel  . '.log';
    }

    private static function defaultChannel(&$channel = '')
    {
        if (empty($channel)) {
            if (PHP_SAPI == 'cli') {
                $channel = 'cli';
            } else {
                $channel = 'app';
            }
        }
    }

    protected function parseLog($info)
    {
        return implode(PHP_EOL, $info) . PHP_EOL;
    }
    public function save(array $log = [], $append = false)
    {
        $destination = $this->getMasterLogFile();
        $path = dirname($destination);
        !is_dir($path) && mkdir($path, 0755, true);
        foreach ($log as $type => $val) {
            $destination = $path . DIRECTORY_SEPARATOR . $this->fileName($type);
            $this->write($val, $destination, false, $append);
        }
        return true;
    }
    /**
     * 日志写入
     * @access protected
     * @param  array     $message 日志信息
     * @param  string    $destination 日志文件
     * @param  bool      $apart 是否独立文件写入
     * @param  bool      $append 是否追加请求信息
     * @return bool
     */
    protected function write($message, $destination, $apart = false, $append = false)
    {
        // 检测日志文件大小，超过配置大小则备份日志文件重新生成
        $this->checkLogSize($destination);
        $msg = $this->parseLog($message);
        return error_log($msg, 3, $destination);
    }
}