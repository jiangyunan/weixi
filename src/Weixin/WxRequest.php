<?php
namespace Weixin;

/**
 * 微信CURL基础请求接口
 * Class WeixiRequest
 * @package Weixin
 */
class  WxRequest
{
    private $url;
    private $httpCode;
    private $httpInfo;
    private $debug = false;
    private $timeout = 30;
    private $connecttimeout = 30;
    private $ssl = false;
    private $format = 'json';
    private $useragent = 'Weixin OAuth2 v0.1';
    private $postdata = '';
    private $httpHeader = [];
    private static $boundary;

    public static function buildHttpQueryMulti($params)
    {
        if (! $params) {
            return '';
        }

        uksort($params, 'strcmp');
        self::$boundary = $boundary = uniqid('------------------');
        $MPboundary = '--' . $boundary;
        $endMPboundary = $MPboundary . '--';
        $multipartbody = '';
        foreach ($params as $parameter => $value) {
            if (in_array($parameter, array(
                    'pic',
                    'image'
                )) && $value{0} == '@') {
                $url = ltrim($value, '@');
                $content = file_get_contents($url);
                $array = explode('?', basename($url));
                $filename = $array[0];
                $multipartbody .= $MPboundary . "\r\n";
                $multipartbody .= 'Content-Disposition: form-data; name="' . $parameter . '"; filename="' . $filename . '"' . "\r\n";
                $multipartbody .= "Content-Type: image/unknown\r\n\r\n";
                $multipartbody .= $content . "\r\n";
            } else {
                $multipartbody .= $MPboundary . "\r\n";
                $multipartbody .= 'content-disposition: form-data; name="' . $parameter . "\"\r\n\r\n";
                $multipartbody .= $value . "\r\n";
            }
        }
        $multipartbody .= $endMPboundary;
        return $multipartbody;
    }

    /**
     * 获取头部
     * @param $header
     * @return int
     */
    public function getHeader($header)
    {
        $i = strpos($header, ':');
        if (! empty($i)) {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
            $this->httpHeader[$key] = $value;
        }
        return strlen($header);
    }

    /**
     * Get
     * @param $url
     * @param array $parameters
     * @return mixed
     */
    public function get($url, $parameters = array())
    {
        $response = $this->oAuthRequest($url, 'GET', $parameters);
        if ($this->format === 'json') {
            return json_decode($response, true);
        }
        return $response;
    }

    /**
     * POST
     * @param $url
     * @param array $parameters
     * @param bool $multi
     * @return mixed
     */
    public function post($url, $parameters = array(), $multi = false)
    {
        $response = $this->oAuthRequest($url, 'POST', $parameters, $multi);
        if ($this->format === 'json') {
            return json_decode($response, true);
        }
        return $response;
    }

    /**
     * 删除
     * @param $url
     * @param array $parameters
     * @return mixed
     */
    public function delete($url, $parameters = array())
    {
        $response = $this->oAuthRequest($url, 'DELETE', $parameters);
        if ($this->format === 'json') {
            return json_decode($response, true);
        }
        return $response;
    }


    /**
     * 请求
     * @param $url
     * @param $method
     * @param null $postfields
     * @param array $headers
     * @return mixed
     */
    protected function http($url, $method, $postfields = NULL, $headers = array())
    {
        $this->httpInfo = array();
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_URL, $url);
        //curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        //curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        //curl_setopt($ci, CURLOPT_ENCODING, "");
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST,$this->ssl);
        curl_setopt($ci, CURLOPT_HEADER, FALSE);
        switch ($method) {
            case 'POST':
                if (version_compare(phpversion(), '5.5.0', 'ge')) {
                    //针对PHP>5.5 的 CURL 需要增加参数
                    curl_setopt($ci, CURLOPT_SAFE_UPLOAD, false);
                }
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (! empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                    $this->postdata = $postfields;
                }
                break;
            case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (! empty($postfields)) {
                    $url = "{$url}?{$postfields}";
                }
        }
        // if (isset($this->access_token) && $this->access_token)
        // $headers[] = "Authorization: OAuth2 " . $this->access_token;
        $response = curl_exec($ci);
        $this->httpCode = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $this->httpInfo = array_merge($this->httpInfo, curl_getinfo($ci));
        $this->url = $url;
        if ($this->debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);
            echo '=====info=====' . "\r\n";
            print_r(curl_getinfo($ci));
            echo '=====$response=====' . "\r\n";
            print_r($response);
        }

        if (curl_errno($ci)) {
            throw new WxClientException(curl_error($ci));
        }
        curl_close($ci);
        return $response;
    }

    protected function oAuthRequest($url, $method, $parameters, $multi = false)
    {
        switch ($method) {
            case 'GET':
                $url = $url . '?' . http_build_query($parameters);
                $respone = $this->http($url, 'GET');
                break;
            default:
                $headers = array();
                if (! $multi) {
                    if ((is_array($parameters) || is_object($parameters))) {
                        $body = $parameters;
                    } else {
                        $body = $parameters;
                        $headers[] = "Content-Length: " . strlen($body);
                    }
                } else {
                    $body = self::buildHttpQueryMulti($parameters);
                    $headers[] = "Content-Type: multipart/form-data; boundary=" . self::$boundary;
                }

                $respone = $this->http($url, $method, $body, $headers);
        }

        if (isset($respone['errcode']) && $respone['errcode'] != 0) {
            throw new WxClientException($respone['errmsg'], $respone['errcode']);
        }

        return $respone;
    }
}