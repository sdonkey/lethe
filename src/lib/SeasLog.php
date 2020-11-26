<?php
namespace Lethe\Lib;

/**
 * @author neeke@php.net
 * Date: 14-1-27 下午4:47
 */

define('SEASLOG_ALL', 'ALL');
define('SEASLOG_DEBUG', 'DEBUG');
define('SEASLOG_INFO', 'INFO');
define('SEASLOG_NOTICE', 'NOTICE');
define('SEASLOG_WARNING', 'WARNING');
define('SEASLOG_ERROR', 'ERROR');
define('SEASLOG_CRITICAL', 'CRITICAL');
define('SEASLOG_ALERT', 'ALERT');
define('SEASLOG_EMERGENCY', 'EMERGENCY');
define('SEASLOG_DETAIL_ORDER_ASC', 1);
define('SEASLOG_DETAIL_ORDER_DESC', 2);

class SeasLog
{
    public function __construct()
    {
        #SeasLog init
    }

    public function __destruct()
    {
        #SeasLog distroy
    }

    /**
     * 设置basePath
     *
     * @param $basePath
     *
     * @return bool
     */
    public static function setBasePath($basePath)
    {
        return true;
    }

    /**
     * 获取basePath
     *
     * @return string
     */
    public static function getBasePath()
    {
        return 'the base_path';
    }

    /**
     * 设置本次请求标识
     * @param string
     * @param mixed $request_id
     * @return bool
     */
    public static function setRequestID($request_id)
    {
        return true;
    }

    /**
     * 获取本次请求标识
     * @return string
     */
    public static function getRequestID()
    {
        return uniqid();
    }

    /**
     * 设置模块目录
     * @param $module
     *
     * @return bool
     */
    public static function setLogger($module)
    {
        return true;
    }

    /**
     * 获取最后一次设置的模块目录
     * @return string
     */
    public static function getLastLogger()
    {
        return 'the lastLogger';
    }

    /**
     * 设置DatetimeFormat配置
     * @param $format
     *
     * @return bool
     */
    public static function setDatetimeFormat($format)
    {
        return true;
    }

    /**
     * 返回当前DatetimeFormat配置格式
     * @return string
     */
    public static function getDatetimeFormat()
    {
        return 'the datetimeFormat';
    }

    /**
     * 统计所有类型（或单个类型）行数
     * @param string $level
     * @param string $log_path
     * @param null $key_word
     *
     * @return array | long
     */
    public static function analyzerCount($level = 'all', $log_path = '*', $key_word = null)
    {
        return [];
    }

    /**
     * 以数组形式，快速取出某类型log的各行详情
     *
     * @param        $level
     * @param string $log_path
     * @param null $key_word
     * @param int $start
     * @param int $limit
     * @param        $order
     *
     * @return array
     */
    public static function analyzerDetail($level = SEASLOG_INFO, $log_path = '*', $key_word = null, $start = 1, $limit = 20, $order = SEASLOG_DETAIL_ORDER_ASC)
    {
        return [];
    }

    /**
     * 获得当前日志buffer中的内容
     *
     * @return array
     */
    public static function getBuffer()
    {
        return [];
    }

    /**
     * 将buffer中的日志立刻刷到硬盘
     *
     * @return bool
     */
    public static function flushBuffer()
    {
        return true;
    }

    /**
     * 记录debug日志
     *
     * @param        $message
     * @param array $content
     * @param string $module
     */
    public static function debug($message, array $content = [], $module = '')
    {
        #$level = SEASLOG_DEBUG
    }

    /**
     * 记录info日志
     *
     * @param        $message
     * @param array $content
     * @param string $module
     */
    public static function info($message, array $content = [], $module = '')
    {
        #$level = SEASLOG_INFO
    }

    /**
     * 记录notice日志
     *
     * @param        $message
     * @param array $content
     * @param string $module
     */
    public static function notice($message, array $content = [], $module = '')
    {
        #$level = SEASLOG_NOTICE
    }

    /**
     * 记录warning日志
     *
     * @param        $message
     * @param array $content
     * @param string $module
     */
    public static function warning($message, array $content = [], $module = '')
    {
        #$level = SEASLOG_WARNING
    }

    /**
     * 记录error日志
     *
     * @param        $message
     * @param array $content
     * @param string $module
     */
    public static function error($message, array $content = [], $module = '')
    {
        #$level = SEASLOG_ERROR
    }

    /**
     * 记录critical日志
     *
     * @param        $message
     * @param array $content
     * @param string $module
     */
    public static function critical($message, array $content = [], $module = '')
    {
        #$level = SEASLOG_CRITICAL
    }

    /**
     * 记录alert日志
     *
     * @param        $message
     * @param array $content
     * @param string $module
     */
    public static function alert($message, array $content = [], $module = '')
    {
        #$level = SEASLOG_ALERT
    }

    /**
     * 记录emergency日志
     *
     * @param        $message
     * @param array $content
     * @param string $module
     */
    public static function emergency($message, array $content = [], $module = '')
    {
        #$level = SEASLOG_EMERGENCY
    }

    /**
     * 通用日志方法
     * @param        $level
     * @param        $message
     * @param array $content
     * @param string $module
     */
    public static function log($level, $message, array $content = [], $module = '')
    {
    }
}
