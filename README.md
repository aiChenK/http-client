# HttpClient
简易http请求类

## 运行环境
- PHP 5.5+
- curl extension

## 安装方法
1. 根目录运行

        composer require aichenk/http-client
        
2. 在`composer.json`中声明

        "require": {
            "aichenk/http-client": "^1.0"
        }
            


## 使用
```php
$client = new Client('http://aichenk.com');
//$client->verifySSL(false);
$response = $client->get('/check.php', ['a' => 1]);
 
echo $response->getBody();
```

## 更新日志
2019-08-11 1.0.2
- 更改setConnectTimeout|setTimeout实现方式
- 增加libcurl库版本>=7.16.2要求