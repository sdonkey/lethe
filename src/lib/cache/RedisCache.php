<?php
namespace Lethe\Lib\Cache;

class RedisCache implements CacheInterface
{
    /**
     * @var object | Redis
     */
    private $handle;
    /**
     * @param object $handle
     */
    public function __construct($handle)
    {
        $this->handle = $handle;
    }
    /**
     * @param string $key
     * @param mixed  $val
     * @param int    $expire
     * @return bool
     */
    public function set($key, $val, $expire = 0)
    {
        if ($expire > 0) {
            return $this->handle->setex($key, $expire, $val);
        }
        return $this->handle->set($key, $val);
    }
    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->handle->get($key);
    }

    public function increment($key, $val = 1)
    {
        return $this->handle->incrBy($key, abs((int) $val));
    }

    /**
     * @param string $key
     * @return bool
     */
    public function del($key)
    {
        return $this->handle->del($key);
    }
}
