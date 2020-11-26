<?php
/**
 * Cache Interface
 *
 * @author wuqiying@ruijie.com.cn
 */
namespace Lethe\Lib\Cache;

interface CacheInterface
{
    /**
     * @param string $key
     * @param mixed  $val
     * @param int    $expire
     * @return bool
     */
    public function set($key, $val, $expire);
    /**
     * @param string $key
     * @return mixed
     */
    public function get($key);
    /**
     * @param string $key
     * @return bool
     */
    public function del($key);
}
