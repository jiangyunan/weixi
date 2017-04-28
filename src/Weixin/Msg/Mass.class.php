<?php
/**
 * Created by PhpStorm.
 * User: jiang
 * Date: 17-3-13
 * Time: 上午9:47
 */

namespace Weixin\Msg;
use Weixin\WxBase;
use Weixin\WxClientException;

/**
 * 在公众平台网站上，为订阅号提供了每天一条的群发权限，
 * 为服务号提供每月（自然月）4条的群发权限。
 * 而对于某些具备开发能力的公众号运营者，
 * 可以通过高级群发接口，实现更灵活的群发能力。
 *
 * 请注意：
 *
 * 1、该接口暂时仅提供给已微信认证的服务号
 * 2、虽然开发者使用高级群发接口的每日调用限制为100次，但是用户每月只能接收4条，请小心测试
 * 3、无论在公众平台网站上，还是使用接口群发，用户每月只能接收4条群发消息，多于4条的群发将对该用户发送失败。
 * 4、具备微信支付权限的公众号，在使用高级群发接口上传、群发图文消息类型时，可使用<a>标签加入外链
 *
 */
class Mass extends WxBase
{
    /**
     * 群发地址
     * @var string
     */
    protected $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/';

    /**
     * 是否可以转发
     * @var int
     */
    private $sendIgnoreRepint = 1;

    /**
     * 根据分组进行群发
     *
     * @param array $params
     * @throws WxClientException
     * @return array
     */
    public function sendAll($params)
    {
        $params['send_ignore_reprint'] = $this->sendIgnoreRepint;
        if (is_array($params)) {
            $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        } else {
            $json = $params;
        }
        $rst = $this->request->post($this->url . 'sendall?access_token=' . $this->token, $json);
        // 返回结果
        if (! empty($rst['errcode'])) {
            throw new WxClientException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }
    /**
     * 根据OpenID列表群发
     *
     * @param array $params
     * @throws WxClientException
     * @return array
     */
    public function send($params)
    {
        if (is_array($params)) {
            $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        } else {
            $json = $params;
        }
        $rst = $this->request->post($this->url . 'send?access_token=' . $this->token, $json);
        // 返回结果
        if (! empty($rst['errcode'])) {
            throw new WxClientException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 删除群发
     *
     * @param string $msgid
     * @throws WxClientException
     * @return array
     */
    public function delete($msgid)
    {
        $ret = array();
        $ret['msgid'] = $msgid;

        $json = json_encode($ret, JSON_UNESCAPED_UNICODE);
        $rst = $this->request->post($this->url . 'delete?access_token=' . $this->token, $json);
        // 返回结果
        if (! empty($rst['errcode'])) {
            throw new WxClientException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 预览接口【订阅号与服务号认证后均可用】
     * 开发者可通过该接口发送消息给指定用户，在手机端查看消息的样式和排版。
     *
     * @param array $params
     * @throws WxClientException
     * @return array
     */
    public function preview($params)
    {
        if (is_array($params)) {
            $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        } else {
            $json = $params;
        }

        $rst = $this->request->post($this->url . 'preview?access_token=' . $this->token, $json);
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
     * @param string $group_id
     * @param string $content
     * @param string $title
     * @param string $description
     * @param bool $isToAll
     * @return array
     */
    public function sendTextByGroup($group_id, $content, $title = "", $description = "", $isToAll = false)
    {
        $ret = array();
        $ret['filter']['group_id'] = $group_id;
        if ($isToAll) {
            $ret['filter']['is_to_all'] = $this->$isToAll;
        }
        $ret['msgtype'] = 'text';
        $ret['text']['content'] = $content;
        $ret['text']['title'] = $title;
        $ret['text']['description'] = $description;
        return $this->sendAll($ret);
    }

    /**
     * 发送图片消息
     *
     * @param string $group_id
     * @param string $media_id
     * @param string $title
     * @param string $description
     * @param bool $isToAll
     * @return array
     */
    public function sendImageByGroup($group_id, $media_id, $title = "", $description = "",  $isToAll = false)
    {
        $ret = array();
        $ret['filter']['group_id'] = $group_id;
        if ($isToAll) {
            $ret['filter']['is_to_all'] = $isToAll;
        }
        $ret['msgtype'] = 'image';
        $ret['image']['media_id'] = $media_id;
        $ret['image']['title'] = $title;
        $ret['image']['description'] = $description;
        return $this->sendAll($ret);
    }

    /**
     * 发送语音消息
     *
     * @param string $group_id
     * @param string $media_id
     * @param string $title
     * @param string $description
     * @param bool $isToAll
     * @return array
     */
    public function sendVoiceByGroup($group_id, $media_id, $title = "", $description = "", $isToAll = false)
    {
        $ret = array();
        $ret['filter']['group_id'] = $group_id;
        if ($isToAll) {
            $ret['filter']['is_to_all'] = $isToAll;
        }
        $ret['msgtype'] = 'voice';
        $ret['voice']['media_id'] = $media_id;
        $ret['voice']['title'] = $title;
        $ret['voice']['description'] = $description;
        return $this->sendAll($ret);
    }

    /**
     * 发送视频消息
     *
     * @param string $group_id
     * @param string $media_id
     * @param string $title
     * @param string $description
     * @param bool $isToAll
     * @return array
     */
    public function sendVideoByGroup($group_id, $media_id, $title = "", $description = "", $isToAll = false)
    {
        $ret = array();
        $ret['filter']['group_id'] = $group_id;
        if ($isToAll) {
            $ret['filter']['is_to_all'] = $isToAll;
        }
        $ret['msgtype'] = 'mpvideo';
        $ret['mpvideo']['media_id'] = $media_id;
        $ret['mpvideo']['title'] = $title;
        $ret['mpvideo']['description'] = $description;
        return $this->sendAll($ret);
    }

    /**
     * 发送图文消息
     *
     * @param string $group_id
     * @param string $media_id
     * @param string $title
     * @param string $description
     * @param bool $isToAll
     * @return array
     */
    public function sendGraphTextByGroup($group_id, $media_id, $title = "", $description = "", $isToAll = false)
    {
        $ret = array();
        $ret['filter']['group_id'] = $group_id;
        if ($isToAll) {
            $ret['filter']['is_to_all'] = $this->$isToAll;
        }
        $ret['msgtype'] = 'mpnews';
        $ret['mpnews']['media_id'] = $media_id;
        $ret['mpnews']['title'] = $title;
        $ret['mpnews']['description'] = $description;
        return $this->sendAll($ret);
    }

    /**
     * 发送卡券消息
     *
     * @param string $group_id
     * @param string $card_id
     * @param array $card_ext
     * @param bool $isToAll
     * @return array
     */
    public function sendWxcardByGroup($group_id, $card_id, array $card_ext, $isToAll = false)
    {
        $ret = array();
        $ret['filter']['group_id'] = $group_id;
        if ($isToAll) {
            $ret['filter']['is_to_all'] = $isToAll;
        }
        $ret['msgtype'] = 'wxcard';
        $ret['wxcard']['card_id'] = $card_id;
        $ret['wxcard']['card_ext'] = json_encode($card_ext);
        return $this->sendAll($ret);
    }

    /**
     * 发送图片消息
     *
     * @param array $toUsers
     * @param string $media_id
     * @param string $title
     * @param string $description
     * @return array
     */
    public function sendImageByOpenid(array $toUsers, $media_id, $title = "", $description = "")
    {
        $ret = array();
        $ret['touser'] = $toUsers;
        $ret['msgtype'] = 'image';
        $ret['image']['media_id'] = $media_id;
        $ret['image']['title'] = $title;
        $ret['image']['description'] = $description;
        return $this->send($ret);
    }
    /**
     * 发送语音消息
     *
     * @param array $toUsers
     * @param string $media_id
     * @param string $title
     * @param string $description
     * @return array
     */
    public function sendVoiceByOpenid(array $toUsers, $media_id, $title = "", $description = "")
    {
        $ret = array();
        $ret['touser'] = $toUsers;
        $ret['msgtype'] = 'voice';
        $ret['voice']['media_id'] = $media_id;
        $ret['voice']['title'] = $title;
        $ret['voice']['description'] = $description;
        return $this->send($ret);
    }
    /**
     * 发送视频消息
     *
     * @param array $toUsers
     * @param string $media_id
     * @param string $title
     * @param string $description
     * @return array
     */
    public function sendVideoByOpenid(array $toUsers, $media_id, $title = "", $description = "")
    {
        $ret = array();
        $ret['touser'] = $toUsers;
        $ret['msgtype'] = 'mpvideo';
        $ret['mpvideo']['media_id'] = $media_id;
        $ret['mpvideo']['title'] = $title;
        $ret['mpvideo']['description'] = $description;
        return $this->send($ret);
    }
    /**
     * 发送图文消息
     *
     * @param array $toUsers
     * @param string $media_id
     * @param string $title
     * @param string $description
     * @return array
     */
    public function sendGraphTextByOpenid(array $toUsers, $media_id, $title = "", $description = "")
    {
        $ret = array();
        $ret['touser'] = $toUsers;
        $ret['msgtype'] = 'mpnews';
        $ret['mpnews']['media_id'] = $media_id;
        $ret['mpnews']['title'] = $title;
        $ret['mpnews']['description'] = $description;
        return $this->send($ret);
    }
    /**
     * 发送卡券消息
     *
     * @param array $toUsers
     * @param string $card_id
     * @param array $card_ext
     * @return array
     */
    public function sendWxcardByOpenid(array $toUsers, $card_id, array $card_ext)
    {
        $ret = array();
        $ret['touser'] = $toUsers;
        $ret['msgtype'] = 'wxcard';
        $ret['wxcard']['card_id'] = $card_id;
        $ret['wxcard']['card_ext'] = json_encode($card_ext);
        return $this->send($ret);
    }


}