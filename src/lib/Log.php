<?php
namespace Lethe\Lib;

/**
 * Log
 *
 * @author liweixin@ruijie.com.cn
 */

class Log
{
    /**
     * 记录debug日志
     *
     * @param        $message
     * @param array $content
     * @param string $module
     */
    public static function debug($message, $content = [], $module = 'default')
    {
        SeasLog::debug($message, $content, $module);
    }

    /**
     * 记录info日志
     *
     * @param        $message
     * @param array $content
     * @param string $module
     */
    public static function info($message, $content = [], $module = 'default')
    {
        SeasLog::info($message, $content, $module);
    }

    /**
     * 记录warning日志
     *
     * @param        $message
     * @param array $content
     * @param string $module
     */
    public static function warning($message, $content = [], $module = 'default')
    {
        SeasLog::warning($message, $content, $module);
    }

    /**
     * 记录error日志
     *
     * @param        $message
     * @param array $content
     * @param string $module
     */
    public static function error($message, $content = [], $module = 'default')
    {
        SeasLog::error($message, $content, $module);
    }
}
