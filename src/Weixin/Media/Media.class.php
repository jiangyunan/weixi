<?php
/**
 * Created by PhpStorm.
 * User: jiang
 * Date: 17-3-13
 * Time: 上午11:29
 */

namespace Weixin\Media;

use Weixin\WxBase;
use Weixin\WxClientException;

/**
 * 素材管理
 * 公众号在使用接口时，
 * 对多媒体文件、多媒体消息的获取和调用等操作，
 * 是通过media_id来进行的。通过本接口，
 * 公众号可以上传或下载多媒体文件。
 * 但请注意，每个多媒体文件（media_id）会在上传、
 * 用户发送到微信服务器3天后自动删除，以节省服务器资源。
 *
 */
class Media extends WxBase
{
    private $uploadNewsKeys = ['thumb_media_id', 'title', 'content'];
    const TYPE_IMAGE = 'image';
    const TYPE_VOICE = 'voice';
    const TYPE_VIDEO = 'video';
    const TYPE_THUMB = 'thumb';
    /**
     * 上传图文消息内的图片获取URL
     * @var string
     */
    private $imageurl = 'https://api.weixin.qq.com/cgi-bin/media/uploadimg';

    /**
     * 上传图文消息素材
     * @var string
     */
    private $newsurl = 'https://api.weixin.qq.com/cgi-bin/media/uploadnews';

    /**
     * 上传临时素材
     * @var string
     */
    private $uploadurl = 'https://api.weixin.qq.com/cgi-bin/media/upload';

    /**
     * 上传永久素材
     * @var string
     */
    private $uploadMaterial = 'https://api.weixin.qq.com/cgi-bin/material/add_news';


    /**
     * 上传图文消息内的图片获取URL【订阅号与服务号认证后均可用】
     * 请注意，本接口所上传的图片不占用公众号的素材库中图片数量的5000个的限制。
     * 图片仅支持jpg/png格式，大小必须在1MB以下。
     *
     * 返回说明 正常情况下的返回结果为：
        {
        "url":  "http://mmbiz.qpic.cn/mmbiz/gLO17UPS6FS2xsypf378iaNhWacZ1G1UplZYWEYfwvuU6Ont96b1roYs CNFwaRrSaKTPCUdBK9DgEHicsKwWCBRQ/0"
        }
        其中url就是上传图片的URL，可用于后续群发中，放置到图文消息中。
     *
     * @param $media
     * @return mixed
     * @throws WxClientException
     */
    public function uploadImage($media)
    {
        $this->verifyImage($media);

        $params['media'] = '@'.$media;

        $rst = $this->request->post($this->imageurl . '?access_token=' . $this->token,
            $params);
        // 返回结果
        if (! empty($rst['errcode'])) {
            throw new WxClientException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 上传图文消息素材【订阅号与服务号认证后均可用】
     * 返回数据示例（正确时的JSON返回结果）：
        {
        "type":"news",
        "media_id":"CsEf3ldqkAYJAU6EJeIkStVDSvffUJ54vqbThMgplD-VJXXof6ctX5fI6-aYyUiQ",
        "created_at":1391857799
        }
     * type	媒体文件类型，分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb），图文消息（news）
        media_id	媒体文件/图文消息上传后获取的唯一标识
        created_at	媒体文件上传时间
     * @param $params
     * @return mixed
     * @throws WxClientException
     */
    public function uploadNews($params)
    {
        //检查参数是否完全
        $this->verifyUploadNewsParams($params);
        $params = json_encode(['articles' => $params], JSON_UNESCAPED_UNICODE);
        $rst = $this->request->post($this->newsurl . '?access_token=' . $this->token, $params);
        // 返回结果
        if (! empty($rst['errcode'])) {
            throw new WxClientException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 新增临时素材
     * 注意点：
            1、临时素材media_id是可复用的。
            2、媒体文件在微信后台保存时间为3天，即3天后media_id失效。
            3、上传临时素材的格式、大小限制与公众平台官网一致。
            图片（image）: 2M，支持PNG\JPEG\JPG\GIF格式
            语音（voice）：2M，播放长度不超过60s，支持AMR\MP3格式
            视频（video）：10MB，支持MP4格式
            缩略图（thumb）：64KB，支持JPG格式
            4、需使用https调用本接口。
     *
     * 正确情况下的返回JSON数据包结果如下：
        {"type":"TYPE","media_id":"MEDIA_ID","created_at":123456789}
     * @param $type
     * @param $media
     */
    public function upload($media, $type=self::TYPE_IMAGE)
    {
        $media = ['media' => '@' . $media];
        $url = $this->uploadurl . '?access_token=' . $this->token . '&type=' . $type;
        $rst = $this->request->post($url, $media);

        // 返回结果
        if (! empty($rst['errcode'])) {
            throw new WxClientException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 对于常用的素材，开发者可通过本接口上传到微信服务器，永久使用。新增的永久素材也可以在公众平台官网素材管理模块中查询管理。
        请注意：
        1、最近更新：永久图片素材新增后，将带有URL返回给开发者，开发者可以在腾讯系域名内使用（腾讯系域名外使用，图片将被屏蔽）。
        2、公众号的素材库保存总数量有上限：图文消息素材、图片素材上限为5000，其他类型为1000。
        3、素材的格式大小等要求与公众平台官网一致：
        图片（image）: 2M，支持bmp/png/jpeg/jpg/gif格式
        语音（voice）：2M，播放长度不超过60s，mp3/wma/wav/amr格式
        视频（video）：10MB，支持MP4格式
        缩略图（thumb）：64KB，支持JPG格式
        4、图文消息的具体内容中，微信后台将过滤外部的图片链接，图片url需通过"上传图文消息内的图片获取URL"接口上传图片获取。
        5、"上传图文消息内的图片获取URL"接口所上传的图片，不占用公众号的素材库中图片数量的5000个的限制，图片仅支持jpg/png格式，大小必须在1MB以下。
     * @param $params
     */
    public function uploadMaterial($params)
    {
        //TODO:上传永久素材
    }

    /**
     * 图片仅支持jpg/png格式，大小必须在1MB以下
     * @param $img
     * @return bool
     * @throws WxClientException
     */
    private function verifyImage($img)
    {
        //是否存在
        if (!file_exists($img)) {
            throw new WxClientException($img . '文件不存在');
        }

        //图片大小
        $fileSize = filesize($img);
        if ($fileSize > (1024 * 1024)) {
            throw new WxClientException($img . '文件大于1m');
        }

        if ($fileSize <= 0) {
            throw new WxClientException($img . '不能上传空文件');
        }

        //图片类型
        $type = pathinfo($img, PATHINFO_EXTENSION);

        if ($type != 'jpg' && $type != 'png') {
            throw new WxClientException($img . '图片类型不匹配' . $type);
        }

        return true;
    }

    /**
     * 检查参数
     * @param $params
     * @throws WxClientException
     */
    private function verifyUploadNewsParams($params)
    {
        foreach ($params as $v) {
            $keys = array_keys($v);
            if (!empty(array_intersect(array_diff($keys, $this->uploadNewsKeys), $this->uploadNewsKeys))) {
                throw new WxClientException('参数不全' . json_encode($v));
            }
        }
    }
}