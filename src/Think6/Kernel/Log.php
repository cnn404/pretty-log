<?php
/*
* @desc: 日志类
* @author： coralme
* @date: 2024/7/19 17:44
*/

namespace PrettyLog\Think6\Kernel;

use PrettyLog\InterfaceLog;
use think\Log as BasicLog;
use PrettyLog\Think6\Kernel\ChannelSet;
use PrettyLog\Think6\Kernel\Channel;

class Log extends BasicLog implements InterfaceLog
{

    protected $namespace = "\\PrettyLog\\Think6\\Kernel\\";
    private $channel = 'app';

    public function record($msg, string $type = 'info', array $context = [], bool $lazy = true)
    {
        $channel = $this->getConfig('type_channel.' . $type);
        $msg = Tool::jsonMessage($msg, $type, $this->channel, $context);
        $this->channel($channel)->record($msg, $type, $context, $lazy, $this->channel);
        return $this;
    }

    public function channel($name = null)
    {
        if (is_array($name)) {
            return new ChannelSet($this, $name);
        }
        $channel = $this->driver($name);
        return $channel;
    }

    public function createDriver(string $name)
    {
        //存在递归继承，需要绕过BasicLog的createDriver方法
        $driver = \think\Manager::createDriver($name);
        $lazy = !$this->getChannelConfig($name, "realtime_write", false) && !$this->app->runningInConsole();
        $allow = array_merge($this->getConfig("level", []), $this->getChannelConfig($name, "level", []));
        return new Channel($name, $driver, $allow, $lazy, $this->app->event);
    }


    public function __call($name, $arguments)
    {
        $names = explode('_', $name);
        if (count($names) == 2) {
            $func = $names[0];
            $channel = $names[1];
            $msg = $arguments[0];
            $context = $arguments[1];
            $this->channel = $channel;
            $this->record($msg, $func, $context);
            $this->channel = 'app';
        } else {
            $this->record($name);
        }
    }
}