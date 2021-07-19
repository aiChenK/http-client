<?php
/**
 * Created by PhpStorm.
 * User: aiChenK
 * Date: 2019-08-08
 * Time: 9:10
 */

namespace HttpClient;

use HttpClient\Core\Response;
use HttpClient\Exception\ConnectionException;

/**
 * Class HttpClient
 * @package HttpClient
 * @method Response getJson(string $path, $query = [])
 * @method Response postJson(string $path, $params = [])
 * @method Response putJson(string $path, $query = [])
 * @method Response patchJson(string $path, $query = [])
 * @method Response deleteJson(string $path, $query = [])
 */
class HttpClient
{
    private $ch;
    private $baseUrl      = '';
    private $options      = [];
    private $headerParams = [];
    private $queryParams  = [];
    private $bodyParams   = [];

    private $connExceptionHandler = null;

    //初始化
    public function __construct($baseUrl = null)
    {
        $this->ch = curl_init();
        //不自动输出
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_ENCODING, 'UTF-8');
        if ($baseUrl) {
            $this->setBaseUrl($baseUrl);
        }
    }

    //销毁
    public function __destruct()
    {
        $this->headerParams = [];
        $this->options = [];
        curl_close($this->ch);
    }

    //设置基础url
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    //设置连接失败回调
    public function setConnExceptionHandle($callback)
    {
        $this->connExceptionHandler = $callback;
        return $this;
    }

    //设置curl属性
    private function setOption($option, $value)
    {
        $this->options[$option] = $value;
        return $this;
    }

    //批量设置curl属性
    public function setOptions($options)
    {
        $this->options = $options + $this->options;
        return $this;
    }

    //验证证书
    public function verifySSL($verify = false)
    {
        $this->setOptions([
            CURLOPT_SSL_VERIFYPEER => $verify,
            CURLOPT_SSL_VERIFYHOST => $verify
        ]);
        return $this;
    }

    //是否跟随跳转
    public function followLocation($follow = false)
    {
        return $this->setOption(CURLOPT_FOLLOWLOCATION, $follow);
    }

    //设置超时时间，libcurl >= 7.16.2
    public function setTimeout($timeout, $ms = false)
    {
        if (!$ms) {
            $this->setOption(CURLOPT_TIMEOUT, $timeout);
        } else {
            $this->setOption(CURLOPT_TIMEOUT_MS, $timeout);
        }
        return $this;
    }

    //设置连接超时时间
    public function setConnectTimeout($timeout, $ms = false)
    {
        if (!$ms) {
            $this->setOption(CURLOPT_CONNECTTIMEOUT, $timeout);
        } else {
            $this->setOption(CURLOPT_CONNECTTIMEOUT_MS, $timeout);
        }
        return $this;
    }

    //设置userAgent
    public function setUserAgent($userAgent)
    {
        return $this->setOption(CURLOPT_USERAGENT, $userAgent);
    }

    //设置http头属性
    public function setHeader($option, $value)
    {
        $this->headerParams[$option] = $value;
        return $this;
    }

    //批量设置http头属性
    public function setHeaders(array $options)
    {
        $this->headerParams = array_merge($this->headerParams, $options);
        return $this;
    }

    //删除http头属性
    public function removeHeader($name)
    {
        unset($this->headerParams[$name]);
        return $this;
    }

    //设置query参数
    public function setQueryParams($params)
    {
        $this->queryParams = $params;
        return $this;
    }

    //设置body参数
    public function setBodyParams($params, $json = false)
    {
        if ($json) {
            $this->setHeader('Content-Type', 'application/json');
            $params = json_encode($params, JSON_UNESCAPED_UNICODE);
        }
        $this->bodyParams = $params;
        return $this;
    }

    //处理并设置http头
    private function resolveHeader()
    {
        if (!empty($this->headerParams)) {
            $headerLine = [];
            foreach ($this->headerParams as $field => $value) {
                $headerLine[] = $field . ': ' . $value;
            }
            $this->setOption(CURLOPT_HTTPHEADER, $headerLine);
        }
    }

    //处理正文
    private function resolveBody($useEncoding = true)
    {
        if (is_array($this->bodyParams)) {
            //有文件则不使用urlencode
            foreach ($this->bodyParams as $param) {
                if ((is_string($param) && strpos($param, '@') === 0) || ($param instanceof \CURLFile)) {
                    $useEncoding = false;
                    break;
                }
            }
            if ($useEncoding) {
                $this->bodyParams = http_build_query($this->bodyParams);
            }
        }
        $this->setOption(CURLOPT_POSTFIELDS, $this->bodyParams ?: '');
    }

    /**
     * GET方式请求
     *
     * @param $path
     * @param array $query
     * @return Response
     * @throws \Exception
     *
     * create by ck 20190808
     */
    public function get($path, $query = [])
    {
        $this->setQueryParams($query);
        $this->setOptions([
            CURLOPT_HTTPGET => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ]);

        return $this->send($path);
    }

    /**
     * POST方式请求
     *
     * @param $path
     * @param array $params
     * @param bool $useEncoding
     * @return Response
     * @throws \Exception
     *
     * create by ck 20190808
     */
    public function post($path, $params = [], $useEncoding = true)
    {
        $this->setBodyParams($params);
        $this->setOptions([
            CURLOPT_POST => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
        ]);

        return $this->send($path, $useEncoding);
    }

    /**
     * DELETE方式请求
     *
     * @param $path
     * @param array $query
     * @return Response
     * @throws \Exception
     *
     * create by ck 20190808
     */
    public function delete($path, $query = [])
    {
        $this->setQueryParams($query);
        $this->setOptions([
            CURLOPT_HTTPGET => true,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
        ]);

        return $this->send($path);
    }

    /**
     * PUT方式请求
     *
     * @param $path
     * @param array $params
     * @param bool $useEncoding
     * @return Response
     * @throws \Exception
     *
     * create by ck 20190808
     */
    public function put($path, $params = [], $useEncoding = true)
    {
        $this->setBodyParams($params);
        $this->setOptions([
            CURLOPT_CUSTOMREQUEST => 'PUT',
        ]);

        return $this->send($path, $useEncoding);
    }

    /**
     * PATCH方式请求
     *
     * @param $path
     * @param array $params
     * @param bool $useEncoding
     * @return Response
     * @throws \Exception
     *
     * create by ck 20190808
     */
    public function patch($path, $params = [], $useEncoding = true)
    {
        $this->setBodyParams($params);
        $this->setOptions([
            CURLOPT_CUSTOMREQUEST => 'PATCH',
        ]);

        return $this->send($path, $useEncoding);
    }

    /**
     * Json请求方式
     *
     * @param $method
     * @param $args
     * @return false|mixed
     *
     * @author aiChenK
     */
    public function __call($method, $args)
    {
        //Json结尾快捷方式
        if (substr($method, -4) === 'Json') {
            $method = substr($method, 0, -4);
            if (!in_array($method, ['get', 'post', 'put', 'patch', 'delete'])) {
                throw new \BadMethodCallException();
            }
            $this->setHeader('Content-Type', 'application/json');
            if (isset($args[1])) {
                $args[1] = json_encode($args[1], JSON_UNESCAPED_UNICODE);
            }
            return call_user_func_array([$this, $method], $args);
        }
        throw new \BadMethodCallException();
    }

    /**
     * 发送
     *
     * @param string $path
     * @param bool $useEncoding
     * @return Response
     * @throws \Exception
     *
     * create by ck 20190808
     */
    private function send($path = '', $useEncoding = true)
    {
        //处理query参数
        if ($this->queryParams) {
            $path .= '?' . (is_array($this->queryParams) ? http_build_query($this->queryParams) : $this->queryParams);
        }

        //处理url
        $url = $this->baseUrl
            ? rtrim($this->baseUrl, '/ ') . '/' . ltrim($path, '/ ')
            : $path;
        $this->setOption(CURLOPT_URL, $url);

        //处理header
        $this->resolveHeader();

        //处理body正文，有文件则不进行encode
        $this->resolveBody($useEncoding);

//        $this->setOption(CURLOPT_NOBODY, true);   //只获取头部
        $this->setOption(CURLOPT_HEADER, true); //获取头部
        @curl_setopt_array($this->ch, $this->options);

        $result = curl_exec($this->ch);

        return $this->resolveResponse($result);
    }

    /**
     * @param $result
     * @return Response
     * @throws ConnectionException
     */
    private function resolveResponse($result)
    {
        if ($result === false) {
            if ($this->connExceptionHandler !== null) {
                call_user_func($this->connExceptionHandler, curl_error($this->ch));
            } else {
                throw new ConnectionException(curl_error($this->ch));
            }
        }
        return new Response($result, $this->ch, $this->bodyParams);
    }
}