<?php
/**
 * Created by PhpStorm.
 * User: jiang
 * Date: 17-3-13
 * Time: 下午1:51
 */
namespace Weixin;

use Weixin\WxHelper;

/**
 * 接收事件推送
 * Class Reply
 * @package Weixin\Msg
 */
class WxEvent
{
    private $subscribeEvent;
    private $unsubscribeEvent;
    private $scanEvent;
    private $locationEvent;
    private $clickEvent;
    private $viewEvent;
    private $textEvent;
    private $imageEvent;
    private $voiceEvent;
    private $videoEvent;
    private $shortvideoEvent;
    private $linkEvent;

    /**
     * 处理返回
     * @param $xml
     */
    public function run($xml)
    {
        $data = WxHelper::xmlToArray($xml);
        if (isset($data['MsgType'])) {
            if ($data['MsgType'] == 'event') {
                $event = strtolower($data['Event']) . 'Event';
            } else {
                $event = strtolower($data['MsgType']) . 'Event';
            }
            if (property_exists($this, $event) && !empty($this->$event)) {
                return call_user_func($this->$event, $data);
            }
        }
        return '';
    }

    /**
     * 订阅
     * @param callable $fun
     * @return $this
     */
    public function setSubscribe(callable $fun)
    {
        $this->subscribeEvent = $fun;
        return $this;
    }

    /**
     * 取消订阅
     * @param callable $fun
     * @return $this
     */
    public function setUnsubscribe(callable $fun)
    {
        $this->unsubscribeEvent = $fun;
        return $this;
    }

    /**
     * 扫描二维码
     * @param callable $fun
     * @return $this
     */
    public function setScan(callable $fun)
    {
        $this->scanEvent = $fun;
        return $this;
    }

    /**
     * 地理位置
     * @param callable $fun
     * @return $this
     */
    public function setLocation(callable $fun)
    {
        $this->locationEvent = $fun;
        return $this;
    }

    /**
     * 点击
     * @param callable $fun
     * @return $this
     */
    public function setClick(callable $fun)
    {
        $this->clickEvent = $fun;
        return $this;
    }

    /**
     * 查看
     * @param callable $fun
     * @return $this
     */
    public function setView(callable $fun)
    {
        $this->viewEvent = $fun;
        return $this;
    }

    /**
     * 文本消息
     * @param callable $fun
     * @return $this
     */
    public function setText(callable $fun)
    {
        $this->textEvent = $fun;
        return $this;
    }

    /**
     * 图片消息
     * @param callable $fun
     * @return $this
     */
    public function setImage(callable $fun)
    {
        $this->imageEvent = $fun;
        return $this;
    }

    /**
     * 音频消息
     * @param callable $fun
     * @return $this
     */
    public function setVoice(callable $fun)
    {
        $this->voiceEvent = $fun;
        return $this;
    }

    /**
     * 视频消息
     * @param callable $fun
     * @return $this
     */
    public function setVideo(callable $fun)
    {
        $this->videoEvent = $fun;
        return $this;
    }

    /**
     * 短视频消息
     * @param callable $fun
     * @return $this
     */
    public function setShortvideo(callable $fun)
    {
        $this->shortvideoEvent = $fun;
        return $this;
    }

    /**
     * 链接消息
     * @param callable $fun
     * @return $this
     */
    public function setLink(callable $fun)
    {
        $this->linkEvent = $fun;
        return $this;
    }
}