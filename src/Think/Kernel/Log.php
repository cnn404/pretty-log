<?php
/*
* @desc: 自定义Log类
* @author： coralme
* @date: 2024/5/17 9:24
*/

namespace PrettyLog\Think\Kernel;

use PrettyLog\InterfaceLog;
use think\Loader;
use think\Log as BasicLog;

class Log extends BasicLog implements InterfaceLog
{
    public function init($config = []):Log
    {
        $type = isset($config['type']) ? $config['type'] : 'File';

        $this->config = $config;

        unset($config['type']);

        if (!empty($config['close'])) {
            $this->allowWrite = false;
        }

        $this->driver = Loader::factory($type, '\\PrettyLog\\Think\\Kernel\\', $config);

        return $this;
    }

    /**
     * 记录日志信息
     * @access public
     * @param mixed $msg 日志信息
     * @param string $type 日志级别
     * @param array $context 替换内容
     * @param array $channel 日志分类
     * @return $this
     */
    public function record($msg, $type = 'info', array $context = [], $channel = 'app'): Log
    {
        if (!$this->allowWrite) {
            return $this;
        }
        if (PHP_SAPI == 'cli') {
            if (empty($this->config['level']) || in_array($type, $this->config['level'])) {
                // 命令行日志实时写入
                $this->write($msg, $type, true);
            }
        } else {
            $this->log[$channel][] = Tool::jsonMessage($msg,$type,$channel,$context);
        }

        return $this;
    }

    /**
     * 实时写入日志信息 并支持行为
     * @access public
     * @param  mixed  $msg   调试信息
     * @param  string $type  日志级别
     * @param  bool   $force 是否强制写入
     * @return bool
     */
    public function write($msg, $type = 'info', $force = false)
    {
        // 封装日志信息
        if (empty($this->config['level'])) {
            $force = true;
        }

        if (true === $force || in_array($type, $this->config['level'])) {
//            $log[$type][] = $msg;
            $log['app'][] = Tool::jsonMessage($msg, $type, 'app', null);;
        } else {
            return false;
        }

        // 监听log_write
        $this->app['hook']->listen('log_write', $log);
        // 写入日志
        return $this->driver->save($log, false);
    }

    public function __call($name, $arguments)
    {
        $names = explode('_', $name);
        if (count($names) == 2) {
            $func = $names[0];
            $channel = $names[1];
            $msg = $arguments[0];
            $context = $arguments[1];
            $this->record($msg, $func, $context, $channel);
        } else {
            $this->record($name);
        }
    }

}