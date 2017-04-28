<?php
/**
 * Created by PhpStorm.
 * User: jiang
 * Date: 17-3-13
 * Time: 上午9:42
 */
namespace Weixin;
use Weixin\Media\Media;
use Weixin\Msg\Custom;
use Weixin\Msg\Mass;
use Weixin\Msg\Reply;

/**
 * 微信公众号调用类
 * Class WxClient
 * @package Weixin
 */
class WxClient
{
    /**
     * APPID
     * @var
     */
    private $appid = null;

    /**
     * SECRET
     * @var null
     */
    private $secret = null;

    /**
     * 全局唯一接口调用凭据,access_token的存储至少要保留512个字符空间。
     * access_token的有效期目前为2个小时，需定时刷新，
     * 重复获取将导致上次获取的access_token失效
     *
     * @var null
     */
    private $accessToken = null;

    /**
     * 网页刷新Token
     * 由于access_token拥有较短的有效期，当access_token超时后，
     * 可以使用refresh_token进行刷新，refresh_token有效期为30天，
     * 当refresh_token失效之后，需要用户重新授权。
     * @var null
     */
    private $refreshToken = null;

    private $request = null;

    /**
     * 微信请求URL
     * @var string
     */
    private $url = 'https://api.weixin.qq.com/cgi-bin/';

    /**
     * 调用微信的功能类
     * @var null
     */
    protected $method = null;

    public function __construct($appid, $secret, $accessToken = NULL, $refreshToken = NULL)
    {
        $this->appid = $appid;
        $this->secret = $secret;
        $this->refreshToken = $refreshToken;

        $this->request = new WxRequest();
    }

    public function setToken($token)
    {
        $this->accessToken = $token;
        return $this;
    }

    /**
     * 群发消息
     * @return Mass
     */
    public function getMassMsg()
    {
        if ($this->method instanceof Mass) {
            return $this->method;
        }

        $this->method = new Mass($this->accessToken, $this->request);
        return $this->method;
    }

    /**
     * 客服消息
     * @return null|Custom
     */
    public function getCustomMsg()
    {
        if ($this->method instanceof Custom) {
            return $this->method;
        }

        $this->method = new Custom($this->accessToken, $this->request);
        return $this->method;
    }

    /**
     * 多媒体接口
     * @return null|Media
     */
    public function getMedia()
    {
        if ($this->method instanceof Media) {
            return $this->method;
        }

        $this->method = new Media($this->accessToken, $this->request);
        return $this->method;
    }

    /**
     * 自动回复
     * @return null|Reply
     */
    public function getReply()
    {
        if ($this->method instanceof Reply) {
            return $this->method;
        }

        $this->method = new Reply();
        return $this->method;
    }

    /**
     * 获取access_token
     * access_token是公众号的全局唯一票据，
     * 公众号调用各接口时都需使用access_token。
     * 正常情况下access_token有效期为7200秒，
     * 重复获取将导致上次获取的access_token失效。
     * 公众号可以使用AppID和AppSecret调用本接口来获取access_token。
     * AppID和AppSecret可在开发模式中获得（需要已经成为开发者，且帐号没有异常状态）。
     * 注意调用所有微信接口时均需使用https协议。
     */
    public function getAccessToken()
    {
        // http请求方式: GET
        // https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=APPID&secret=APPSECRET
        $params = array();
        $params['grant_type'] = 'client_credential';
        $params['appid'] = $this->appid;
        $params['secret'] = $this->secret;
        $rst = $this->request->get($this->url . 'token', $params);
        if (! empty($rst['errcode'])) {
            // 错误时微信会返回错误码等信息，JSON数据包示例如下（该示例为AppID无效错误）:
            // {"errcode":40013,"errmsg":"invalid appid"}
            throw new WxClientException($rst['errmsg'], $rst['errcode']);
        } else {
            // 正常情况下，微信会返回下述JSON数据包给公众号：
            // {"access_token":"ACCESS_TOKEN","expires_in":7200}
            // 参数 说明
            // access_token 获取到的凭证
            // expires_in 凭证有效时间，单位：秒
            $this->accessToken = $rst['access_token'];
            $rst['grant_type'] = 'client_credential';
        }
        return $rst;
    }

    /**
     * 有效性校验
     * @param String $verifyCode
     */
    public function verify($verifyCode)
    {
        $echoStr = isset($_GET["echostr"]) ? trim($_GET["echostr"]) : '';
        if (! empty($echoStr)) {
            if ($this->checkSignature($verifyCode)) {
                exit($echoStr);
            }
        }
    }

    /**
     * 签名校验
     *
     * @param string $verifyCode
     * @return boolean
     */
    public function checkSignature($verifyCode)
    {
        if (empty($verifyCode))
            throw new WxClientException("请设定校验签名所需的verify_code");

        $verifyCode = trim($verifyCode);
        $signature = isset($_GET['signature']) ? trim($_GET['signature']) : '';
        $timestamp = isset($_GET['timestamp']) ? trim($_GET['timestamp']) : '';
        $nonce = isset($_GET['nonce']) ? trim($_GET['nonce']) : '';
        $tmpArr = array(
            $verifyCode,
            $timestamp,
            $nonce
        );
        sort($tmpArr, SORT_STRING); // 按照字符串来进行比较，否则在某些数字的情况下，sort的结果与微信要求不符合，官方文档中给出的签名算法有误
        $tmpStr = sha1(implode($tmpArr));
        return $tmpStr === $signature ? true : false;
    }
}