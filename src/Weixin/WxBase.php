<?php
namespace Weixin;

/**
 * 基础类
 * Class WxBase
 * @package Weixin
 */
abstract class WxBase
{
    protected $url;
    protected $request;
    protected $token;
    public function __construct($token, WxRequest $request)
    {
        $this->request = $request;
        $this->token = $token;
    }
}