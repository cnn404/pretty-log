<?php
/*
* @desc: 文件日志处理类
* @author： coralme
* @date: 2024/7/22 14:28
*/

namespace PrettyLog\Think6\Kernel;

use think\App;
use think\log\driver\File as BasicFile;

class File extends BasicFile
{

    private string $channel = 'app';

    protected function getMasterLogFile(): string
    {

        if ($this->config['max_files']) {
            $files = glob($this->config['path'] . '*.log');

            try {
                if (count($files) > $this->config['max_files']) {
                    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
                    });
                    unlink($files[0]);
                    restore_error_handler();
                }
            } catch (\Exception $e) {
                //
            }
        }

        if ($this->config['single']) {
            $name = is_string($this->config['single']) ? $this->config['single'] : 'single';
            $destination = $this->config['path'] . $name . '.log';
        } else {

            if ($this->config['max_files']) {
                $filename = date('Ymd') . '.log';
            } else {
                $filename = date('Ym') . DIRECTORY_SEPARATOR . $this->fileName($this->channel);
            }

            $destination = $this->config['path'] . $filename;
        }

        return $destination;
    }

    public function save(array $log): bool
    {
        $destination = $this->getMasterLogFile();

        $path = dirname($destination);
        !is_dir($path) && mkdir($path, 0755, true);
        $info = [];
        foreach ($log as $type => $val) {
            $destination = $path . DIRECTORY_SEPARATOR . $this->fileName($type);
            $this->write($val, $destination);
        }
        return true;
    }


    public function fileName($channel = ''): string
    {
        self::defaultChannel($channel);
        return $channel . '_' . date('Ymd') . '.log';
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
}