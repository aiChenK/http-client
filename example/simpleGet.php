<?php
/**
 * Created by PhpStorm.
 * User: aiChenK
 * Date: 2019-07-17
 * Time: 14:59
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use HttpClient\HttpClient;


try {
    $client = new HttpClient('http://test.aichenk.com');
    $client->setConnExceptionHandle('errorMsg');
    //$client->verifySSL(false);
    $client->setConnectTimeout(1);
    $response = $client->get('/http/index.php', ['a' => 1]);

    if (!$response->isSuccess()) {
        //do something
        //throw new \Exception('request error');
    }
    echo $response->getBody();
    //print_r($response->getJsonBody());
} catch (\Throwable $e) {
    echo 'Exception:' . $e->getMessage();
}


function errorMsg($msg) {
    var_dump($msg);die;
}