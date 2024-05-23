<?php

namespace PrettyLog;

class LogTag
{
    //请求入参
    const REQUEST = 'request';
    //请求响应
    const RESPONSE = 'response';
    //sql记录
    const SQL_MONITOR = 'sql_monitor';
    //调用外部接口入参
    const SERVICE_REQUEST = 'service_request';
    //调用外部接口出参
    const SERVICE_RESPONSE = 'service_response';
    //内部异常’500‘错误
    const APP_EXCEPTION = 'app_exception';
}