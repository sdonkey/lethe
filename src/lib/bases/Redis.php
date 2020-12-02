<?php
/**
 * Base Redis
 *
 * @author wuqiying@ruijie.com.cn
 */
namespace Lethe\Lib\Bases;

use Lethe\Lib\Config;

class Redis
{
    /**
     * @param int $select_db
     * @return \Redis
     */
    public static function getInstance($select_db = 0)
    {
        $instance = \Yaf\Registry::get('REDIS_INSTANCE'.$select_db);

        if (!$instance) {
            $instance = self::connect($select_db);
            \Yaf\Registry::set('REDIS_INSTANCE'.$select_db, $instance);
        }

        return $instance;
    }

    /**
     * @param int $select_db
     * @return \Redis
     */
    private static function connect($select_db = 0)
    {
        $config = Config::get('redis');

        $redis = new \Redis();
        $redis->connect($config['host'], $config['port']);
        $redis->auth($config['auth']);
        $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
        $redis->select($select_db);

        return $redis;
    }
}
