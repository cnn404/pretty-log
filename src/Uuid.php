<?php

/*
* @desc: uuid
* @author： coralme
* @date: 2024/5/1610:00
*/

namespace PrettyLog;
class Uuid
{
    static public function uuid4(): string
    {
        try {
            $randomBytes = random_bytes(16);
            $randomBytes[6] = chr(ord($randomBytes[6]) & 0x0f | 0x40);
            $randomBytes[8] = chr(ord($randomBytes[8]) & 0x3f | 0x80);
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($randomBytes), 4));
        } catch (\Exception $e) {
            return uniqid();
        }
    }
}