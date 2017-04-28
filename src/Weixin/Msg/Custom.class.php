<?php
/**
 * Created by PhpStorm.
 * User: jiang
 * Date: 17-3-13
 * Time: 上午9:50
 */
namespace Weixin\Msg;

use Weixin\WxBase;
use Weixin\WxClientException;

/**
 * 微信客服接口
 * Class Custom
 * @package Weixin\Msg
 */
class Custom extends WxBase
{
    const URL = 'https://api.weixin.qq.com/cgi-bin/message/custom/send';
    /**
     * 发送消息
     * 该接口用于发送从iwebsite 微信模块发送过来的消息
     *
     * @param string $msg
     * @throws WxClientException
     * @return string
     */
    public function send($msg)
    {
        if (is_array($msg)) {
            $json = json_encode($msg, JSON_UNESCAPED_UNICODE);
        } else {
            $json = $msg;
        }
        $rst = $this->request->post(self::URL . '?access_token=' . $this->token, $json);
        // 返回结果
        if (! empty($rst['errcode'])) {
            throw new WxClientException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 发送文本消息
     *
     * @param string $toUser
     * @param string $content
     * @return string
     */
    public function sendText($toUser, $content)
    {
        $ret = array();
        $ret['touser'] = $toUser;
        $ret['msgtype'] = "text";
        $ret['text']["content"] = $content;
        return $this->send($ret);
    }

    /**
     * 发送图片消息
     *
     * @param string $toUser
     * @param string $media_id
     * @return string
     */
    public function sendImage($toUser, $media_id)
    {
        $ret = array();
        $ret['touser'] = $toUser;
        $ret['msgtype'] = "image";
        $ret['image']["media_id"] = $media_id;
        return $this->send($ret);
    }
    /**
     * 发送语音消息
     *
     * @param string $toUser
     * @param string $media_id
     * @return string
     */
    public function sendVoice($toUser, $media_id)
    {
        $ret = array();
        $ret['touser'] = $toUser;
        $ret['msgtype'] = "voice";
        $ret['voice']["media_id"] = $media_id;
        return $this->send($ret);
    }
    /**
     * 发送视频消息
     *
     * @param string $toUser
     * @param string $media_id
     * @param string $thumb_media_id
     * @param string $title
     * @param string $description
     * @return string
     */
    public function sendVideo($toUser, $media_id, $thumb_media_id, $title, $description)
    {
        $ret = array();
        $ret['touser'] = $toUser;
        $ret['msgtype'] = "video";
        $ret['video']['media_id'] = $media_id;
        $ret['video']['thumb_media_id'] = $thumb_media_id;
        $ret['video']['title'] = $title;
        $ret['video']['description'] = $description;
        return $this->send($ret);
    }
    /**
     * 发送音乐消息
     *
     * @param string $toUser
     * @param string $title
     * @param string $description
     * @param string $musicurl
     * @param string $hqmusicurl
     * @param string $thumb_media_id
     * @return string
     */
    public function sendMusic($toUser, $title, $description, $musicurl, $hqmusicurl, $thumb_media_id)
    {
        $hqmusicurl = $hqmusicurl == '' ? $musicurl : $hqmusicurl;
        $ret = array();
        $ret['touser'] = $toUser;
        $ret['msgtype'] = "video";
        $ret['music']["title"] = $title;
        $ret['music']["description"] = $description;
        $ret['music']["musicurl"] = $musicurl;
        $ret['music']["hqmusicurl"] = $hqmusicurl;
        $ret['music']["thumb_media_id"] = $thumb_media_id;
        return $this->send($ret);
    }
    /**
     * 发送图文消息
     *
     * @param string $toUser
     * @param array $articles
     * @return string
     */
    public function sendGraphText($toUser, Array $articles)
    {
        if (! is_array($articles) || count($articles) == 0)
            return '';
        $items = array();
        $articles = array_slice($articles, 0, 8); // 图文消息条数限制在8条以内。
        foreach ($articles as $article) {
            $items[] = array(
                'title' => $article['title'],
                'description' => $article['description'],
                'url' => $article['url'],
                'picurl' => $article['picurl']
            );
        }
        $ret = array();
        $ret['touser'] = $toUser;
        $ret['msgtype'] = "news";
        $ret['news']["articles"] = $items;
        return $this->send($ret);
    }

    /**
     * 发送图文消息
     *
     * @param string $toUser
     * @param array $articles
     * @return string
     */
    public function sendMediaId($toUser, $mediaId)
    {
        $ret = array();
        $ret['touser'] = $toUser;
        $ret['msgtype'] = "mpnews";
        $ret['mpnews']["media_id"] = $mediaId;
        return $this->send($ret);
    }

    /**
     * 发送卡券消息
     * 特别注意客服消息接口投放卡券仅支持非自定义Code码的卡券。
     *
     * @param string $toUser
     * @param string $card_id
     * @param array $card_ext
     * @return string
     */
    public function sendWxcard($toUser, $card_id, array $card_ext)
    {
        $ret = array();
        $ret['touser'] = $toUser;
        $ret['msgtype'] = 'wxcard';
        $ret['wxcard']['card_id'] = $card_id;
        $ret['wxcard']['card_ext'] = json_encode($card_ext);
        return $this->send($ret);
    }
}