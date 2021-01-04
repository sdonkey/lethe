<?php
namespace Lethe\Lib;
/* **********************************************************
 *
 * @author: wangjianrong<wangjr129@163.com>
 * @Date: 2019/1/2 12:41
 * @copyright Copyright 2018-2023 © www.ruiJie.com.cn All rights reserved.
 * @license http://www.ruijie.com.cn/gy/
 * 安全类
 * *********************************************************** */

class Secure
{
    /**
     * @param string $var 要过滤的变量字符串或数组
     *
     * @return string  处理后的变量
     */
    public static function htmlspecialchars(&$var)
    {
        return htmlspecialchars($var, ENT_QUOTES);
    }

    /**
     * @param string  $var  要过滤的变量字符串或数组
     * @param mixed $string
     *
     * @return string 处理后的变量
     */
    public static function htmlspecialcharsDecode(&$string)
    {
        return htmlspecialchars_decode($string, ENT_QUOTES);
    }

    public static function xssEncodeForImg($str)
    {
        $pattern_str = "/(<img) ([\s\S]*?src\s*=\s*[\" | \'](.*?)[\"|\'][\s\S]*?)(>)/";
        $match_num = preg_match_all($pattern_str, $str, $match_src);
        if ($match_num < 0) {
            return $str;
        }

        $replace_str = "[img $2]";
        return preg_replace($pattern_str, $replace_str, $str);
    }

    public static function xssDecodeForImg($str)
    {
        $pattern_str = "/(\[img) ([\s\S]*?src\s*=\s*[\" | \'](.*?)[\"|\'][\s\S]*?)(\])/";
        $match_num = preg_match_all($pattern_str, $str, $match_src);
        if ($match_num < 0) {
            return $str;
        }

        $replace_str = "<img $2>";
        return preg_replace($pattern_str, $replace_str, $str);
    }

    /**
     * sql like搜索特殊字符处理
     * @param $name
     * @return string|string[]
     */
    public static function sqlSearch($name){
        $name = addcslashes($name, '_%\\^');
        return $name;
    }
}
