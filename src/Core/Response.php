<?php
/**
 * Created by PhpStorm.
 * User: aiChenK
 * Date: 2019-08-08
 * Time: 9:10
 */

namespace HttpClient\Core;

class Response
{
    private $rawHeader;
    private $rawBody;

    private $code;
    private $header;

    private $info;

    /**
     * Response constructor.
     * @param $result
     * @param $ch
     * @param $requestBody
     * @param $options
     */
    public function __construct($result, $ch, $requestBody, $options)
    {
        $result = explode("\r\n\r\n", $result);
        $this->rawBody   = array_pop($result);
        $this->rawHeader = array_pop($result);

        $this->code      = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->_parseHeader();
        $this->_parseInfo($ch, $requestBody, $options);
    }

    private function _parseHeader()
    {
        $headers = array_filter(explode("\r\n", $this->rawHeader));
        foreach ($headers as $field) {
            if (!is_array($field)) {
                $field = array_map('trim', explode(':', $field, 2));
            }
            if (count($field) == 2) {
                $this->header[$field[0]] = $field[1];
            }
        }
    }

    private function _parseInfo($ch, $requestBody, $options)
    {
        $this->info               = new Info();
        $this->info->requestBody  = $requestBody;
        $this->info->url          = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $this->info->method       = $options[CURLOPT_CUSTOMREQUEST] ?? 'GET';
        $this->info->httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->info->requestSize  = curl_getinfo($ch, CURLINFO_REQUEST_SIZE);
        $this->info->totalTime    = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        $this->info->connectTime  = curl_getinfo($ch, CURLINFO_CONNECT_TIME);
        $this->info->responseBody = &$this->rawBody;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function getBody()
    {
        return $this->rawBody;
    }

    public function getJsonBody($assoc = true)
    {
        return json_decode($this->rawBody, $assoc);
    }

    //same as is2xx
    public function isSuccess()
    {
        return $this->is2xx();
    }

    public function is2xx()
    {
        return substr($this->code, 0, 1) == 2;
    }

    public function is4xx()
    {
        return substr($this->code, 0, 1) == 4;
    }

    public function is5xx()
    {
        return substr($this->code, 0, 1) == 5;
    }

    /**
     * 返回请求信息
     *
     * @return Info
     */
    public function getInfo()
    {
        return $this->info;
    }
}
