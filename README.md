## PrettyLog日志组件

PrettyLog v2.0.4 日志组件支持打印Phal和Think框架的全链路日志。

### 调用接口

```php
#业务逻辑埋点：
\PrettyLog\Phal\Logger::getInstance()->info('pretty_log1',['a'=>1]);
\PrettyLog\Phal\Logger::getInstance()->debug('pretty_log1',['a'=>1]);
\PrettyLog\Phal\Logger::getInstance()->error('pretty_log1',['a'=>1]);

\PrettyLog\Think\Logger::getInstance()->info('pretty_log1',['a'=>1]);
\PrettyLog\Think\Logger::getInstance()->debug('pretty_log1',['a'=>1]);
\PrettyLog\Think\Logger::getInstance()->error('pretty_log1',['a'=>1]);

#特定场景埋点：
\PrettyLog\Phal\Logger::AppRequest($data);
\PrettyLog\Phal\Logger::AppResponse($data);
\PrettyLog\Phal\Logger::ServiceRequest($data);
\PrettyLog\Phal\Logger::ServiceResponse($data);
\PrettyLog\Phal\Logger::Sql($data);

\PrettyLog\Think\Logger::AppRequest($data);
\PrettyLog\Think\Logger::AppResponse($data);
\PrettyLog\Think\Logger::ServiceRequest($data);
\PrettyLog\Think\Logger::ServiceResponse($data);
\PrettyLog\Think\Logger::Sql($data);
```
### 写入规格

由于云日志平台对json格式能很好的解析查看，`pretty-log`的每条日志都是一个`json字符串`

`message`：用户日志的key，需要注意的是以下五种场景的message为固定值

```php
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
    //视图接口输出参数
    const VIEW_RESPONSE = 'view_response';
```

`context`：日志关联的结构化数据

普通日志：
```json
{"message":"your message","datetime":"2024-05-21 16:01:14","level_name":"info","channel":"app","context":{"a":5,"b":2},"request_id":"f91d4b33-ec4e-47de-85b8-6410175ff125"}
```
app请求入参日志：
```json
{"message":"request","datetime":"2024-05-22 09:27:10","level_name":"info","client_ip":"172.17.0.1","channel":"app","context":{"method":"POST","url":"http:\/\/1017.test.com\/?s=Customer.App.testLog&b=10","query":"s=Customer.App.testLog&b=10","payload":{"xxx":4654,"fadfa":"1234123"},"header":{"Cookie":"PHPSESSID=8ae46oihidkf5niumbb2ecrffc","Content-Length":"44","Connection":"keep-alive","Accept-Encoding":"gzip, deflate, br","Postman-Token":"5df30d2d-4a77-427f-8c11-3b9522482a04","Cache-Control":"no-cache","Accept":"*\/*","User-Agent":"PostmanRuntime\/7.37.3","Content-Type":"application\/json","App":"name","Host":"1017.test.com"},"client_ip":"172.17.0.1"},"request_id":"10d4d4b3-58eb-4e8a-9a2a-2aa0a6b667e6"}
```
app请求返回日志：
```json
{"message":"response","datetime":"2024-05-22 09:27:10","level_name":"info","client_ip":"172.17.0.1","channel":"app","context":{"ret":0,"data":{},"msg":"test exception","request_id":"10d4d4b3-58eb-4e8a-9a2a-2aa0a6b667e6","cost":389},"request_id":"10d4d4b3-58eb-4e8a-9a2a-2aa0a6b667e6"}
```
sql日志：
```json
{"message":"sql_monitor","datetime":"2024-05-22 09:27:10","level_name":"sql","client_ip":"172.17.0.1","channel":"app","context":{"request":{"service":"Customer.App.testLog"},"sql":"[#2 - 81.76ms - 47.5KB - SQL]\/data\/release\/apps\/r_customer_api\/src\/customer\/Domain\/UserSessionD.php(259):    Customer\\Model\\UserSession::getOneInfoByWhere()    test_custonlineser.users    SELECT * FROM users ORDER BY id desc LIMIT 1;","cost":81.76},"request_id":"10d4d4b3-58eb-4e8a-9a2a-2aa0a6b667e6"}
```
exception日志：

```json
{"message":"app_exception","datetime":"2024-05-22 13:42:45","level_name":"error","channel":"app","context":{"error_message":"[0]xxxxxxxxxxxxxxxxxx4\r\n#0 [internal function]: app\\index\\controller\\Account->testLog()\n#1 \/data\/release\/apps\/r_cms\/thinkphp\/library\/think\/Container.php(395): ReflectionMethod->invokeArgs(Object(app\\index\\controller\\Account), Array)\n#2 \/data\/release\/apps\/r_cms\/thinkphp\/library\/think\/route\/dispatch\/Module.php(131): think\\Container->invokeReflectMethod(Object(app\\index\\controller\\Account), Object(ReflectionMethod), Array)\n#3 [internal function]: think\\route\\dispatch\\Module->think\\route\\dispatch\\{closure}(Object(think\\Request), Object(Closure), NULL)\n#4 \/data\/release\/apps\/r_cms\/thinkphp\/library\/think\/Middleware.php(185): call_user_func_array(Object(Closure), Array)\n#5 [internal function]: think\\Middleware->think\\{closure}(Object(think\\Request))\n#6 \/data\/release\/apps\/r_cms\/thinkphp\/library\/think\/Middleware.php(130): call_user_func(Object(Closure), Object(think\\Request))\n#7 \/data\/release\/apps\/r_cms\/thinkphp\/library\/think\/route\/dispatch\/Module.php(136): think\\Middleware->dispatch(Object(think\\Request), 'controller')\n#8 \/data\/release\/apps\/r_cms\/thinkphp\/library\/think\/route\/Dispatch.php(168): think\\route\\dispatch\\Module->exec()\n#9 \/data\/release\/apps\/r_cms\/thinkphp\/library\/think\/App.php(432): think\\route\\Dispatch->run()\n#10 [internal function]: think\\App->think\\{closure}(Object(think\\Request), Object(Closure), NULL)\n#11 \/data\/release\/apps\/r_cms\/thinkphp\/library\/think\/Middleware.php(185): call_user_func_array(Object(Closure), Array)\n#12 [internal function]: think\\Middleware->think\\{closure}(Object(think\\Request))\n#13 \/data\/release\/apps\/r_cms\/thinkphp\/library\/think\/Middleware.php(130): call_user_func(Object(Closure), Object(think\\Request))\n#14 \/data\/release\/apps\/r_cms\/thinkphp\/library\/think\/App.php(435): think\\Middleware->dispatch(Object(think\\Request))\n#15 \/data\/release\/apps\/r_cms\/public\/index.php(27): think\\App->run()\n#16 {main}"},"request_id":"15ae967d-81f9-4cdf-9813-ddbd10ea69c6"}
```

一个完整的日志链路从 `request` 开始，`response` 结束。

### 安装PrettyLog日志组件

`composer require coralme/pretty-log`

如果开发环境使用`composer`困难，可以使用本地安装形式，将代码下载到本地路径

例如：`/data/release/projects/pretty-log`

修改要接入的项目的composer.json文件，添加如下内容：

```json
  "repositories": [
    {
      "type": "path",
      "url": "/data/release/projects/pretty-log",
      "options": {
        "symlink": false
      }
    }
  ],
```
然后在 `require`模块里添加包名：

`"coralme/pretty-log": "*"`

执行`composer update coralme/pretty-log`命令完成安装


### 1. 接入Phal框架

#### 1.1 初始化DI依赖属性

通常会在该文件引入di服务，找到对应的`di.php`文件,如果没有该文件，用户也可以自己写一个类似的文件，修改全局`DI`的相关依赖属性(删除源赋值)

##### 注意读取配置文件的代码需要前置`$di->config = new FileConfig(API_ROOT . '/config')`

```php
// 配置

$di->config = new FileConfig(API_ROOT . '/config');

$di->request = new \PrettyLog\Phal\Kernel\Request();
$di->logger = new \PrettyLog\Phal\Logger();
$di->response = new \PrettyLog\Phal\Kernel\JsonResponse(JSON_UNESCAPED_UNICODE);
$di->error = new \PrettyLog\Phal\Kernel\ApiError();
```

<strong><span style="color: red;">注意，必须在后续使用这些依赖对象之前进行上述修改</span></strong>



#### 1.2 记录web请求入口的日志

在所有业务逻辑发生之前，至少需要在DI的`request`和`logger`之后才能记录原始请求日志，

```php
$di->request = new \PrettyLog\Phal\Kernel\Request();
$di->logger = new \PrettyLog\Phal\Logger();
\PrettyLog\Phal\Logger::AppRequest();
```


#### 1.3 记录业务逻辑的日志

例如有一个接口testLog,记录如下日志

```php
    public function testLog()
    {
        $this->di->logger->info('hello_pretty_log1', [
            'a'=>1,
            'b'=>2,
            'c'=>3
        ]);
        \PrettyLog\Phal\Logger::getInstance('system')->error('hello_pretty_log2',['a'=>123123]);
        $this->di->logger->debug('hello_pretty_log3:hello123');
        \PrettyLog\Phal\Logger::getInstance()->debug('hello_pretty_log4',['name'=>'mm']);
        return [];
    }
```
注意`$this->di->logger`是兼容了之前代码里大部分写法，
目前推荐使用`\PrettyLog\Phal\Logger::getInstance()`写法，同时从v2.0开始支持批量记录和单条记录，批量记录指在内存中收集20条日志后才记录一次到日志文件中，该请求不足20条或提前退出的，将在请求响应以后再一次性记录，
相比较单次记录会大幅度提升性能，减少磁盘io次数 。日志组件会默认启用批量记录，如果有单次记录，需要在调用日志打印的时候传$once=true。例如
```php

 \PrettyLog\Phal\Logger::getInstance()->info('record_once_sample',['a'=>123123],true);

```

该功能已适配Phal和Think5框架（请注意Think6框架未完全适配）

其中`getInstance`可以传`channel`参数,日志将分文件存放在不同的名称下，默认是 `app`,`runtime`目录如下：
```
.
├── cache
└── log
    ├── 202404
    │   ├── 20240402.log
    │   ├── error_20240402.log
    │   └── notice_20240402.log
    └── 202405
        ├── app_20240516.log
        └── system_20240516.log

4 directories, 5 files

```
#### 1.4 记录响应日志

对于Phal框架，响应日志在PrettyLog组件中会自动记录，业务开发无需理会，具体逻辑在
```php
    #vendor/coralme/pretty-log/src/Phal/Kernel/JsonResponse.php
    public function output()
    {
        $startTime = \PhalApi\DI()->get('start_time');
        if (empty($startTime)) {
            $startTime = microtime(true);
        }
        Logger::AppResponse(array_merge(
            $this->getResult(),
            ['cost' => intval(1000 * (microtime(true) - $startTime))]
        ));
        parent::output();
    }
```
#### 1.5 记录内部接口调用

业务中请求第三方api也需要记录访问请求日志和返回日志

请求日志：
```php
//记录请求前需要保存的日志，通常包含，method,url,body,query等
 Logger::ServiceRequest([]);
```

`CURL/GuzzleHttp`
执行外部请求过程

返回日志：
```php
//记录远程接口返回日志，通常包含请求 cost耗时，响应数据等
Logger::ServiceResponse([]);
```

#### 1.6 记录sql日志

在`sys.php`开启sql日志记录即可自动记录sql日志，sql日志的`message`为`sql_monitor`

```php
    'notorm_debug' => true,

    /**
     * @var boolean 是否纪录SQL到日志，需要同时开启notorm_debug方可写入日志
     */
    'enable_sql_log' => true,
```


#### 1.7 示例日志

app_20240516.log:

```json

{"message":"request","datetime":"2024-05-21 19:29:37","level_name":"info","client_ip":"172.17.0.1","channel":"app","context":{"method":"POST","url":"http:\/\/1017.test.com\/?s=Customer.App.testLog&b=10","query":"s=Customer.App.testLog&b=10","payload":{"xxx":4654,"fadfa":"1234123"},"header":{"Cookie":"PHPSESSID=8ae46oihidkf5niumbb2ecrffc","Content-Length":"44","Connection":"keep-alive","Accept-Encoding":"gzip, deflate, br","Postman-Token":"c9d7cd55-9cf3-4584-9128-18c6268c00a9","Cache-Control":"no-cache","Accept":"*\/*","User-Agent":"PostmanRuntime\/7.37.3","Content-Type":"application\/json","App":"name","Host":"1017.test.com"},"client_ip":"172.17.0.1"},"request_id":"e3dbe4e1-5db4-4763-8a21-d17ca8499b94"}
{"message":"sql_monitor","datetime":"2024-05-21 19:29:38","level_name":"sql","client_ip":"172.17.0.1","channel":"app","context":{"request":{"service":"Customer.App.testLog"},"sql":"[#1 - 21.77ms - 47.9KB - SQL]\/data\/release\/apps\/r_customer_api\/src\/customer\/Domain\/UserSessionD.php(259):    Customer\\Model\\UserSession::getOneInfoByWhere()    test_custonlineser.users    SELECT * FROM users ORDER BY id desc LIMIT 1;","cost":21.77},"request_id":"e3dbe4e1-5db4-4763-8a21-d17ca8499b94"}
{"message":"sql_monitor","datetime":"2024-05-21 19:29:38","level_name":"sql","client_ip":"172.17.0.1","channel":"app","context":{"request":{"service":"Customer.App.testLog"},"sql":"[#2 - 72.2ms - 47.5KB - SQL]\/data\/release\/apps\/r_customer_api\/src\/customer\/Domain\/UserSessionD.php(259):    Customer\\Model\\UserSession::getOneInfoByWhere()    test_custonlineser.users    SELECT * FROM users ORDER BY id desc LIMIT 1;","cost":72.2},"request_id":"e3dbe4e1-5db4-4763-8a21-d17ca8499b94"}
{"message":"sql_monitor","datetime":"2024-05-21 19:29:38","level_name":"sql","client_ip":"172.17.0.1","channel":"app","context":{"request":{"service":"Customer.App.testLog"},"sql":"[#3 - 70.72ms - 47.5KB - SQL]\/data\/release\/apps\/r_customer_api\/src\/customer\/Domain\/UserSessionD.php(259):    Customer\\Model\\UserSession::getOneInfoByWhere()    test_custonlineser.users    SELECT * FROM users ORDER BY id desc LIMIT 1;","cost":70.72},"request_id":"e3dbe4e1-5db4-4763-8a21-d17ca8499b94"}
{"message":"hello_pretty_log2","datetime":"2024-05-21 19:29:38","level_name":"error","client_ip":"172.17.0.1","channel":"app","context":{"a":123123},"request_id":"e3dbe4e1-5db4-4763-8a21-d17ca8499b94"}
{"message":"hello_pretty_log4","datetime":"2024-05-21 19:29:38","level_name":"debug","client_ip":"172.17.0.1","channel":"app","context":{"name":"mm"},"request_id":"e3dbe4e1-5db4-4763-8a21-d17ca8499b94"}
{"message":"response","datetime":"2024-05-21 19:29:38","level_name":"info","client_ip":"172.17.0.1","channel":"app","context":{"ret":200,"data":{},"msg":"","debug":{"stack":["[#1 - 0ms - 2.4MB - PHALAPI_INIT]\/data\/release\/apps\/r_customer_api\/public\/index.php(5)","[#2 - 41.7ms - 2.5MB - PHALAPI_RESPONSE]\/data\/release\/apps\/r_customer_api\/vendor\/phalapi\/kernal\/src\/PhalApi.php(46)","[#3 - 360.7ms - 3.9MB - PHALAPI_FINISH]\/data\/release\/apps\/r_customer_api\/vendor\/phalapi\/kernal\/src\/PhalApi.php(74)"],"sqls":["[#1 - 21.77ms - 47.9KB - SQL]\/data\/release\/apps\/r_customer_api\/src\/customer\/Domain\/UserSessionD.php(259):    Customer\\Model\\UserSession::getOneInfoByWhere()    test_custonlineser.users    SELECT * FROM users ORDER BY id desc LIMIT 1;","[#2 - 72.2ms - 47.5KB - SQL]\/data\/release\/apps\/r_customer_api\/src\/customer\/Domain\/UserSessionD.php(259):    Customer\\Model\\UserSession::getOneInfoByWhere()    test_custonlineser.users    SELECT * FROM users ORDER BY id desc LIMIT 1;","[#3 - 70.72ms - 47.5KB - SQL]\/data\/release\/apps\/r_customer_api\/src\/customer\/Domain\/UserSessionD.php(259):    Customer\\Model\\UserSession::getOneInfoByWhere()    test_custonlineser.users    SELECT * FROM users ORDER BY id desc LIMIT 1;"],"version":"2.23.0"},"request_id":"e3dbe4e1-5db4-4763-8a21-d17ca8499b94","cost":340},"request_id":"e3dbe4e1-5db4-4763-8a21-d17ca8499b94"}

```

system_20240516.log:

```json
{"message":"hello_pretty_log2","datetime":"2024-05-16 15:15:50","level_name":"ERROR","client_ip":"172.17.0.1","context":{"a":123123},"request_id":"18130a3a-e99f-4be4-a03a-70095d8588b6"}
```

#### 1.8 api返回

返回字段会固定添加`request_id`，以便于用户查询日志

```json
{
    "ret": 200,
    "data": {},
    "msg": "",
    "request_id": "18130a3a-e99f-4be4-a03a-70095d8588b6"
}
```

### 2. 接入Think框架
请注意，think5和think6框架差别较大，无法兼容，接入的时候需要注意！！！

特别注意命名空间的区分，`pretty-log`也同样区分了不同的接入组件。

`think5在 src/think`
`think6在 src/think6`

不同版本的框架在调用相关类的时候，需要使用对应的版本，不能混用


#### 2.1 添加全局钩子处理逻辑

##### 2.1.1 http请求

##### think5

找到`index.php`文件，

初始化app的代码：

```php
$container = Container::get('app')->run();
```

修改为：

```php
$app = Container::get('app');
\PrettyLog\Think\Kernel\Tool::HookHandler($app);
$container = $app->run();
```
##### think6
找到`index.php`文件，

初始化app的代码：

```php
// 执行HTTP应用并响应
$http = (new App())->http;
$response = $http->run();
$response->send();
$http->end($response);
```

修改为：

```php
$app = new App();
\PrettyLog\Think6\Kernel\Tool::HookHandler($app);
$http = $app->http;
$response = $http->run();
\PrettyLog\Think6\Kernel\Tool::AppendRequestId($response);
$response->send();
$http->end($response);
```


##### 2.1.2 命令行请求

##### think5

找到`think`文件，通常在项目根目录
初始化app的代码：

```php
Container::get('app')->path(__DIR__ . '/application/')->initialize();
```

修改为：

```php
$app = Container::get('app');
\PrettyLog\Think\Kernel\Tool::HookHandler($app);
$app->path(__DIR__ . '/application/')->initialize();
```
##### think6

找到`think`文件，通常在项目根目录
初始化app的代码：

```php
// 应用初始化
(new App())->console->run();
```

修改为：

```php
$app = new App();
\PrettyLog\Think6\Kernel\Tool::HookHandler($app);
$app->console->run();
```

#### 2.2 修改单个日志文件大小为300MB

##### think5

在 `config/log.php`文件添加 `'file_size'   => 300000000`

##### think6

在`config/log.php`找到 `channels.file`的配置，添加`'file_size'   => 300000000`


#### 2.3 修改异常错误处理器

##### think5

修改`config/app.php`文件的`exception_handle`字段的值，没有该字段则需要添加

```php
// 异常处理handle类 留空使用 \think\exception\Handle
'exception_handle' => PrettyLog\Think\Kernel\Handle::class,
```
##### think6

找到 `app/provider.php`

```php
return [
    'think\Request'          => Request::class,
    'think\exception\Handle' => ExceptionHandle::class,
];
```

修改为：
```php
// 容器Provider定义文件
return [
    'think\Request'          => Request::class,
    'think\exception\Handle' => \PrettyLog\Think6\Kernel\Handle::class,
];
```



#### 2.4 业务逻辑添加日志

### 不同框架业务调用接口完全一致，只需要匹配命名空间即可

```
think5: PrettyLog\Think\Logger 
think6: PrettyLog\Think6\Logger
phal: PrettyLog\Phal\Logger
```


控制器添加以下方法，发送http请求：

```php
    public function testLog()
    {
        #虽然兼容旧的日志记录写法，但是推荐使用新写法Logger::getInstance()->info()

        #region旧的写法
        Log::write("This is old log style.");
        Log::info('xxxxxxxxxxxxxxxxxx1');
        Log::error('xxxxxxxxxxxxxxxxxx2');
        Log::log('debug','xxxxxxxxxxxxxxxxxx3');
        Log::record('hello world', 'error');
        #endregion

        $originRoleInfo = UserGroup::find(1);
        Logger::getInstance()->info('Hello_Pretty_Info2' ,[
            'a'=>5,
            'b'=>2
        ]);
        Logger::getInstance()->info('Hello_Pretty_Info3' ,[
            'a'=>5,
            'b'=>2
        ]);
        Logger::getInstance('app1')->info('Hello_Pretty_Info4' ,[
            'a'=>5,
            'b'=>2
        ]);
    
        Logger::getInstance('app1')->debug('Hello_Pretty_Info5' ,[
            'a'=>5,
            'b'=>2
        ]);
        Logger::getInstance()->debug('Hello_Pretty_Info6' ,[
            'a'=>5,
            'b'=>2
        ]);
        return json([
            "ret" => 0,
            "msg" => "success"
        ]);
    }
```

以上示例虽然兼容旧的写法，但是推荐使用新的记录日志的规范.

```
curl --location '127.0.0.1:8087/index/Account/testLog?hello=333&my=789456' \
--header 'host: cms.test.com' \
--header 'Content-Type: application/json' \
--header 'Cookie: PHPSESSID=8ae46oihidkf5niumbb2ecrffc' \
--data '{
    "axvasdvf":123123,
    "gafads":"fadsfasdfaf"
}'
```

查看runtime目录下的日志：

```
.
├── cache
├── log
│   └── 202405
│       ├── app1_20240521.log
│       └── app_20240521.log
└── temp
    └── aa2f2405c8859ab2f4192694755d6caa.php

```

依据`channel`分类生成两个日志文件

`app1_20240521.log`

```json
{"message":"Hello_Pretty_Info4","datetime":"2024-05-21 16:01:14","level_name":"info","channel":"app1","context":{"a":5,"b":2},"request_id":"f91d4b33-ec4e-47de-85b8-6410175ff125"}
{"message":"Hello_Pretty_Info5","datetime":"2024-05-21 16:01:14","level_name":"debug","channel":"app1","context":{"a":5,"b":2},"request_id":"f91d4b33-ec4e-47de-85b8-6410175ff125"}
```


`app_20240521.log`

```json
{"message":"request","datetime":"2024-07-26 08:28:28","level_name":"info","channel":"app","context":{"method":"GET","url":"http://1016.byd.com:8080/admin/visit.returns/testlog","query":"s=admin/visit.returns/testlog","payload":{"mobile":"18400141024"}},"request_id":"9ff34d9a-113c-48a3-a351-4eb2adadcf1b"}
{"message":"sql_monitor","datetime":"2024-07-26 16:28:28","level_name":"sql","channel":"app","context":{"sql":"CONNECT:[ UseTime:0.030903s ] mysql:host=10.167.96.242;port=3306;dbname=byd_traffic_quality;charset=utf8","cost":-1},"request_id":"9ff34d9a-113c-48a3-a351-4eb2adadcf1b"}
{"message":"sql_monitor","datetime":"2024-07-26 16:28:28","level_name":"sql","channel":"app","context":{"sql":"SHOW FULL COLUMNS FROM `call_visit_returns` ","cost":21},"request_id":"9ff34d9a-113c-48a3-a351-4eb2adadcf1b"}
{"message":"sql_monitor","datetime":"2024-07-26 16:28:28","level_name":"sql","channel":"app","context":{"sql":"SELECT * FROM `call_visit_returns` WHERE  `id` = 0 LIMIT 1 ","cost":23},"request_id":"9ff34d9a-113c-48a3-a351-4eb2adadcf1b"}
{"message":"response","datetime":"2024-07-26 16:28:29","level_name":"info","channel":"app","context":{"code":200,"message":"Hello World!","data":{"name":"EasyAdmin","version":"v1.0.0","time":"2024-07-26 16:28:28"},"cost":713},"request_id":"9ff34d9a-113c-48a3-a351-4eb2adadcf1b"}

```


`pretty-log`会自动添加`请求、响应`日志, 开发只需要关注业务日志，并使用`Logger::getInstance()`进行日志打印， 一个完整的api日志，同样以 `request`开始，`response`结束。

`config/database.php`设置：
```php
// 数据库调试模式
'debug'           => true,
```

开启数据库调试模式，会自动将所有sql的日志也记录下来，`message`固定为`sql_monitor`

#### 2.5 记录内部接口调用

业务中请求第三方api也需要记录访问请求日志和返回日志

请求日志：
```php
//记录请求前需要保存的日志，通常包含，method,url,body,query等
 Logger::ServiceRequest([]);
```

`CURL/GuzzleHttp`
执行外部请求过程

返回日志：
```php
//记录远程接口返回日志，通常包含请求 cost耗时，响应数据等
Logger::ServiceResponse([]);
```
