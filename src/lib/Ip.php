<?php
/**
 * Ip
 *
 * @author wuqiying@ruijie.com.cn
 */

class Ip
{
    /**
     * @return array|false|string
     */
    public static function user()
    {
        if (isset($_SERVER['HTTP_X_USERIP']) && $_SERVER['HTTP_X_USERIP'] && strcasecmp($_SERVER['HTTP_X_USERIP'], 'unknown')) {
            $ip = $_SERVER['HTTP_X_USERIP'];
        } else {
            $ip = self::client();
        }
        return $ip;
    }
    /**
     * @return array|false|string
     */
    public static function client()
    {
        $ip = '';
        if (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
            strpos($ip, ',') && list($ip) = explode(',', $ip);
        } elseif (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    /**
     * @return string
     */
    public static function frontend()
    {
        if (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }
        return '';
    }
    /**
     * @return string
     */
    public static function local()
    {
        if (isset($_SERVER['SERVER_ADDR'])) {
            return $_SERVER['SERVER_ADDR'];
        }
        return '';
    }
}
