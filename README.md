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
2020-06-09 - 2.0.1
- 修复`Response`类中`is5xx`方法

2019-10-17 - 2.0.0
- `Client`命名更改为`HttpClient`
- 增加异常类
- `Response`类增加`getCode|is?xx`方法判断返回值

2019-08-13 - 1.0.3
- php版本要求增加到5.6

2019-08-11 - 1.0.2
- 更改setConnectTimeout|setTimeout实现方式
- 增加libcurl库版本>=7.16.2要求
