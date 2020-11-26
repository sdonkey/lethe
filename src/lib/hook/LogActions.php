<?php
namespace Lethe\Lib\Hook;
/**
 * Created by PhpStorm.
 * User: feng_li@ruijie.com.cn
 * Date: 2018/11/28
 * Time: 14:00
 */

class LogActions
{
    public static function run($info)
    {
        self::logRequestInfo($info);
    }

    public static function logRequestInfo($info)
    {
        $message = 'params:'.$info;
        \Log::error($message, [], 'requestParams');
    }

    public static function dbInfo($info)
    {
        \Log::error($info, [], 'db');
    }
}
