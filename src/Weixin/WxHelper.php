<?php
/**
 * Created by PhpStorm.
 * User: jiang
 * Date: 17-3-13
 * Time: 下午1:59
 */

namespace Weixin;

/**
 * 工具类
 * Class WxHelper
 * @package Weixin
 */
class WxHelper
{
    /**
     * 作用：将xml转为array
     */
    public static function xmlToArray($xml)
    {
        $object = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        return @json_decode(preg_replace('/{}/', '""', @json_encode($object)), 1);
    }

    /**
     * 作用：array转xml
     */
    public static function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";
        return $xml;
    }
}