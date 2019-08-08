<?php
/**
 * Created by PhpStorm.
 * User: aiChenK
 * Date: 2019-07-17
 * Time: 14:59
 */

require_once dirname(__DIR__) . '/src/Bootstrap.php';
\HttpClient\Bootstrap::init();

use HttpClient\Client;

$client = new Client('http://aichenk.com');
//$client->verifySSL(false);
$response = $client->get('/check.php', ['a' => 1]);

echo $response->getBody();
//print_r($response->getJsonBody());

