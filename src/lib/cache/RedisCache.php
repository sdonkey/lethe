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

    /**
     * @param string $key
     * @return int
     */
    public function lLen($key)
    {
        return $this->handle->lLen($key);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function rPop($key)
    {
        $data = $this->handle->rPop($key);
        $data && $data = $this->decodeData($data);
        return $data;
    }

    /**
     * @param string $key
     * @param array $data
     * @return bool|int
     */
    public function lPush($key, $data)
    {
        return $this->handle->lPush($key, $this->encodeData($data));
    }

    /**
     * 序列化数据
     *
     * @param array $data
     *
     * @return string
     */
    protected function encodeData($data)
    {
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode($data);
        }
    }

    /**
     * 反序列化数据
     *
     * @param string $data
     *
     * @return string
     */
    protected function decodeData($data)
    {
        return json_decode($data, true);
    }
}
