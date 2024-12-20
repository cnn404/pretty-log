<?php
/*
* @desc: think6的channel，注意和pretty-log的channel概念区分
 * 只需要理解pretty-log的channel是将日志文件切分到不同的文件中就可以了
* @author： coralme
* @date: 2024/7/23 17:24
*/

namespace PrettyLog\Think6\Kernel;

use think\contract\LogHandlerInterface;
use think\Event;
use think\event\LogRecord;
use think\log\Channel as BasicChannel;

class Channel extends BasicChannel
{
    
    public function record($msg, string $type = 'info', array $context = [], bool $lazy = true,string $channel = 'app')
    {
        if ($this->close || (!empty($this->allow) && !in_array($type, $this->allow))) {
            return $this;
        }

        if (is_string($msg) && !empty($context)) {
            $replace = [];
            foreach ($context as $key => $val) {
                $replace['{' . $key . '}'] = $val;
            }

            $msg = strtr($msg, $replace);
        }

        if (!empty($msg) || 0 === $msg) {
            $this->log[$channel][] = $msg;
            if ($this->event) {
                $this->event->trigger(new LogRecord($type, $msg));
            }
        }

        if (!$this->lazy || !$lazy) {
            $this->save();
        }

        return $this;
    }

}