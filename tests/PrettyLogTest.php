<?php

/*
* @desc: 单元测试
* @author： coralme
* @date: 2024/5/1515:49
*/
use PHPUnit\Framework\TestCase;
use PrettyLog\Phal\Logger;


class PrettyLogTest extends TestCase
{
    public function testPrettyLog()
    {
        $uuid = \PrettyLog\Uuid::uuid4();
        \PhalApi\DI()->set('request_id',$uuid);
        Logger::getInstance()->info('test unit111',['hello'=>'11111']);
        Logger::getInstance('app1')->info('test unit222',['hello'=>'22222']);
        Logger::getInstance('app2')->info('test unit333',['hello'=>'33333']);
        $this->assertEmpty(false);
    }
}