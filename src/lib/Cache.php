<?php
namespace Lethe\Lib;

use Lethe\Lib\Cache\RedisCache;
/**C
 * Cache
 *
 * @author wuqiying@ruijie.com.cn
 */

/**
 * @method static mixed set($key, $val, $expire)
 * @method static mixed get($key)
 * @method static mixed del($key)
 */
class Cache
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
     * @return null | Cache_Redis
     */
    public static function factory($driver, $handle)
    {
        $cache = null;
        switch ($driver) {
            case 'redis':
                $cache = new RedisCache($handle);
                break;
            default:
                throw new Exception('Cache driver empty');
                break;
        }
        return $cache;
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
