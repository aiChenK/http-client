# HttpClient
简易http请求类

> 简单示例
```php
$client = new Client('http://aichenk.com');
//$client->verifySSL(false);
$response = $client->get('/check.php', ['a' => 1]);
 
echo $response->getBody();
```