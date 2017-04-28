<?php
/**
 * Created by PhpStorm.
 * User: jiang
 * Date: 17-3-13
 * Time: 下午3:26
 */

namespace Weixin\Msg;

class Reply
{
    /**
     * 回复文本
     *
     * @param string $toUser
     * @param string $fromUser
     * @param string $content
     * @return string
     */
    public function replyText($toUser, $fromUser, $content)
    {
        $time = time();
        return <<<XML
<xml>
<ToUserName><![CDATA[{$toUser}]]></ToUserName>
<FromUserName><![CDATA[{$fromUser}]]></FromUserName>
<CreateTime>{$time}</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[{$content}]]></Content>
</xml>
XML;
    }
    /**
     * 回复图片消息
     *
     * @param string $toUser
     * @param string $fromUser
     * @param string $media_id
     * @return string
     */
    public function replyImage($toUser, $fromUser, $media_id)
    {
        $time = time();
        return <<<XML
<xml>
<ToUserName><![CDATA[{$toUser}]]></ToUserName>
<FromUserName><![CDATA[{$fromUser}]]></FromUserName>
<CreateTime>{$time}</CreateTime>
<MsgType><![CDATA[image]]></MsgType>
<Image>
<MediaId><![CDATA[{$media_id}]]></MediaId>
</Image>
</xml>
XML;
    }
    /**
     * 回复语音消息
     *
     * @param string $toUser
     * @param string $fromUser
     * @param string $media_id
     * @return string
     */
    public function replyVoice($toUser, $fromUser, $media_id)
    {
        $time = time();
        return <<<XML
<xml>
<ToUserName><![CDATA[{$toUser}]]></ToUserName>
<FromUserName><![CDATA[{$fromUser}]]></FromUserName>
<CreateTime>{$time}</CreateTime>
<MsgType><![CDATA[voice]]></MsgType>
<Voice>
<MediaId><![CDATA[{$media_id}]]></MediaId>
</Voice>
</xml>
XML;
    }
    /**
     * 回复视频消息
     *
     * @param string $toUser
     * @param string $fromUser
     * @param string $media_id
     * @param string $thumb_media_id
     * @return string
     */
    public function replyVideo($toUser, $fromUser, $media_id, $thumb_media_id)
    {
        $time = time();
        return <<<XML
<xml>
<ToUserName><![CDATA[{$toUser}]]></ToUserName>
<FromUserName><![CDATA[{$fromUser}]]></FromUserName>
<CreateTime>{$time}</CreateTime>
<MsgType><![CDATA[video]]></MsgType>
<Video>
<MediaId><![CDATA[{$media_id}]]></MediaId>
<ThumbMediaId><![CDATA[{$thumb_media_id}]]></ThumbMediaId>
</Video>
</xml>
XML;
    }
    /**
     * 回复音乐
     *
     * @param string $toUser
     * @param string $fromUser
     * @param string $title
     * @param string $description
     * @param string $musicUrl
     * @param string $hqMusicUrl
     * @param string $thumbMediaId
     * @return string
     */
    public function replyMusic($toUser, $fromUser, $title, $description, $musicUrl, $hqMusicUrl = '', $thumbMediaId = 0)
    {
        $time = time();
        $hqMusicUrl = $hqMusicUrl == '' ? $musicUrl : $hqMusicUrl;
        $thumbMediaIdXml = empty($thumbMediaId) ? "" : "<ThumbMediaId><![CDATA[{$thumbMediaId}]]></ThumbMediaId>";
        return <<<XML
<xml>
<ToUserName><![CDATA[{$toUser}]]></ToUserName>
<FromUserName><![CDATA[{$fromUser}]]></FromUserName>
<CreateTime>{$time}</CreateTime>
<MsgType><![CDATA[music]]></MsgType>
<Music>
<Title><![CDATA[{$title}]]></Title>
<Description><![CDATA[{$description}]]></Description>
<MusicUrl><![CDATA[{$musicUrl}]]></MusicUrl>
<HQMusicUrl><![CDATA[{$hqMusicUrl}]]></HQMusicUrl>
{$thumbMediaIdXml}
</Music>
</xml>
XML;
    }
    /**
     * 回复图文信息
     *
     * @param string $toUser
     * @param string $fromUser
     * @param array $articles
     *            子元素
     *            $articles[] = $article
     *            子元素结构
     *            $article['title']
     *            $article['description']
     *            $article['picurl'] 图片链接，支持JPG、PNG格式，较好的效果为大图640*320，小图80*80
     *            $article['url']
     *
     * @return string
     */
    public function replyGraphText($toUser, $fromUser, Array $articles)
    {
        $time = time();
        if (! is_array($articles) || count($articles) == 0)
            return '';
        $items = '';
        $articles = array_slice($articles, 0, 10);
        $articleCount = count($articles);
        foreach ($articles as $article) {
            if (mb_strlen($article['description'], 'utf-8') > 30) {
                $article['description'] = mb_substr($article['description'], 0, 30, 'utf-8') . '……';
            }
            $items .= <<<XML
<item>
<Title><![CDATA[{$article['title']}]]></Title>
<Description><![CDATA[{$article['description']}]]></Description>
<PicUrl><![CDATA[{$article['picurl']}]]></PicUrl>
<Url><![CDATA[{$article['url']}]]></Url>
</item>
XML;
        }
        return <<<XML
<xml>
<ToUserName><![CDATA[{$toUser}]]></ToUserName>
<FromUserName><![CDATA[{$fromUser}]]></FromUserName>
<CreateTime>{$time}</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>{$articleCount}</ArticleCount>
<Articles>{$items}</Articles>
</xml>
XML;
    }
    /**
     * 回复客服启动消息
     *
     * @return string
     */
    public function replyCustomerService($toUser, $fromUser, $KfAccount = NULL)
    {
        $time = time();
        if (empty($KfAccount)) {
            return <<<XML
<xml>
<ToUserName><![CDATA[{$toUser}]]></ToUserName>
<FromUserName><![CDATA[{$fromUser}]]></FromUserName>
<CreateTime>{$time}</CreateTime>
<MsgType><![CDATA[transfer_customer_service]]></MsgType>
</xml>
XML;
        } else {
            return <<<XML
<xml>
<ToUserName><![CDATA[{$toUser}]]></ToUserName>
<FromUserName><![CDATA[{$fromUser}]]></FromUserName>
<CreateTime>{$time}</CreateTime>
<MsgType><![CDATA[transfer_customer_service]]></MsgType>
<TransInfo>
   <KfAccount>![CDATA[{$KfAccount}]</KfAccount>
</TransInfo>
</xml>
XML;
        }
    }
}