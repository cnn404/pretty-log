<?php
/*
* @desc: Logger统计规格
* @author： coralme
* @date: 2024/5/17 16:03
*/

namespace PrettyLog;
/**
 * @method error(mixed $message, array $data = []) static 记录error信息
 * @method info(mixed $message, array $data = []) static 记录info信息
 * @method debug(mixed $message, array $data = []) static 记录debug信息
 */
abstract class AbstractLogger implements InterfaceLog
{
    // 日志记录器，think或phal框架的基础日志类
    protected InterfaceLog $logger;
    //单例日志
    protected static AbstractLogger $instance;
    //日志通道
    protected string $channel;

    public static abstract function getInstance($channel = 'app'): AbstractLogger;

    public static abstract function Sql($data);

    public static abstract function AppRequest(array $reqContext = []);

    public static abstract function AppResponse(array $respContext = []);

    public static abstract function ServiceRequest(array $reqContext = []);

    public static abstract function ServiceResponse(array $respContext = []);

}