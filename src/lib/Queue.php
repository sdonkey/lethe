<?php
namespace Lethe\Lib;

use Lethe\Lib\Queue\RedisQueue;
/**
 * Queue
 *
 * @author wuqiying@ruijie.com.cn
 */

/**
 * @method static mixed push($key, $val)
 * @method static mixed pop($key)
 */
class Queue
{
    /**
     * @var callback
     */
    private static $hook;
    /**
     * @param callback $hook
     */
    public static function setHook($hook)
    {
        self::$hook = $hook;
    }
    /**
     * @param string $driver
     * @param object $handle
     * @throws
     * @return null | Queue_Redis
     */
    public static function factory($driver, $handle)
    {
        $queue = null;
        switch ($driver) {
            case 'redis':
                $queue = new \queue\RedisQueue($handle);
                break;
            default:
                throw new Exception('Queue driver empty');
                break;
        }
        return $queue;
    }
    /**
     * @param string $name
     * @param mixed $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        static $driver;
        static $handle;

        if (!$driver || !$handle) {
            list($driver, $handle) = call_user_func(self::$hook);
        }

        return call_user_func_array(
            [
                self::factory($driver, $handle),
                $name
            ],
            $arguments
        );
    }
}
