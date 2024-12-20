<?php
/*
* @desc: ChannelSet
* @author： coralme
* @date: 2024/7/23 17:19
*/

namespace PrettyLog\Think6\Kernel;

use think\log\ChannelSet as BasicChannelSet;

class ChannelSet extends BasicChannelSet
{

    public function __construct(Log $log, array $channels)
    {
        $this->log = $log;
        $this->channels = $channels;
    }
}