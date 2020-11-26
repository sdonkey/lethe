<?php
namespace Lethe\Lib\DB\Cache;

class RedisCache implements Cache
{
    /**
     * @var Mixed
     */
    private $redis;

    public function __construct($redis)
    {
        $this->redis = $redis;
    }
    public function get($keys)
    {
        if (!$keys) {
            return [];
        }
        //获取缓存中值，与keys生成key-value
        $keyValue = array_combine($keys, $this->redis->mget($keys));
        //使外部过去到的为对象
        array_walk($keyValue, function (&$item) {
            $item = json_decode($item);
        });
        //过滤掉空值
        $keyValue = array_filter($keyValue, function ($value) {
            return null !== $value;
        });

        return $keyValue;
    }

    public function set($keyValue)
    {
        $pipe = $this->redis->pipeline();

        foreach ($keyValue as $key => $value) {
            if (null !== $value) {
                $value = json_encode($value);
                $pipe->setex($key, 86400, $value); // 缓存 1 天
            }
        }

        return $pipe->exec();
    }

    public function del($keys)
    {
        return $this->redis->delete($keys);
    }
}
