# HttpClient
简易http请求类

## 运行环境
- PHP 5.6+
- curl extension

## 安装方法
1. 根目录运行

        composer require aichenk/http-client
        
2. 在`composer.json`中声明

        "require": {
            "aichenk/http-client": "^2.0"
        }
            


## 使用
```php
$client = new Client('http://aichenk.com');
//$client->verifySSL(false);
$response = $client->get('/check.php', ['a' => 1]);
 
if (!$response->isSuccess()) {
    //do something
}
echo $response->getBody();
```

## 更新日志
2022-04-24 - v2.1.3
- 修复`Response`高语法问题

2022-04-15 - v2.1.2
- `Response->getInfo()` 方法增加`method`信息

2022-01-05 - v2.1.1
- `HttpClient`增加`resetAfterRequest`构建函数参数、`resetAfterRequest($reset = false)`方法，请求结束后重置请求参数

2021-07-19 - v2.1.0
- `Response`增加`getInfo`方法，用于获取请求数据

2021-01-06 - v2.0.4
- 增加发送json请求快捷方法

2020-08-28 - v2.0.3
- 增加`setConnExceptionHandle`方法自定义处理连接异常

2020-08-02 - v2.0.2
- 所有请求增加`CURLOPT_POSTFIELDS`参数

2020-06-09 - v2.0.1
- 修复`Response`类中`is5xx`方法

2019-10-17 - v2.0.0
- `Client`命名更改为`HttpClient`
- 增加异常类
- `Response`类增加`getCode|is?xx`方法判断返回值

2019-08-13 - v1.0.3
- php版本要求增加到5.6

2019-08-11 - v1.0.2
- 更改setConnectTimeout|setTimeout实现方式
- 增加libcurl库版本>=7.16.2要求
