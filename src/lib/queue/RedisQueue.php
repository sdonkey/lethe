<?php
/**
 * Queue Redis
 *
 * @author wuqiying@ruijie.com.cn
 */
namespace Lethe\Lib\Queue;

class RedisQueue implements QueueInterface
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
     * @return mixed
     */
    public function push($key, $val)
    {
        return $this->handle->lPush($key, $val);
    }
    /**
     * @param string $key
     * @return mixed
     */
    public function pop($key)
    {
        return $this->handle->brpop([$key], 8);
    }
}
