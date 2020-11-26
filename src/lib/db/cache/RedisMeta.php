<?php

namespace Lethe\Lib\DB\Cache;

class RedisMeta implements Meta
{
    private $prefix = 'CM';

    const KEY_SCHEMA_VERSION = 'schema_version';
    const KEY_UPDATE_VERSION = 'update_version';

    /**
     * @var Redis
     */
    private $redis;

    public function __construct($redis)
    {
        $this->redis = $redis;
    }

    /**
     * 生成用于计算key的前缀
     * @param string $db
     * @param string $table
     * @param bool $isForTable 是否为表级缓存
     * @return string
     */
    public function prefix($db, $table, $isForTable = false)
    {
        $version = $this->getSchemaVersion($db, $table);
        if ($isForTable) {
            $version = $version . ':' . $this->getUpdateVersion($db, $table);
        }

        return implode(':', [
            $this->prefix,
            $db,
            $table,
            $version,
        ]);
    }

    /**
     * 获取整个数据库的版本号
     * @param $db
     * @param $table
     * @return int
     */
    private function getSchemaVersion($db, $table)
    {
        //CM:schema_version:rj_u:db
        $key = implode(':', [
            $this->prefix,
            self::KEY_SCHEMA_VERSION,
            $db,
            $table,
        ]);

        $key = md5($key);

        return $this->redis->get($key) ?: 0;
    }

    /**
     * 获取库表版本号
     * @param $db
     * @param $table
     * @return int
     */
    private function getUpdateVersion($db, $table)
    {
        //例如：CM:update_version:rj_u:oc_users;
        $key = implode(':', [
            $this->prefix,
            self::KEY_UPDATE_VERSION,
            $db,
            $table,
        ]);

        $key = md5($key);

        return $this->redis->get($key) ?: 0;
    }

    /**
     * 对表级缓存失效也是利用key incr来完成
     * @param string $db
     * @param string $table
     */
    public function flush($db, $table)
    {
        $key = implode(':', [
            $this->prefix,
            self::KEY_UPDATE_VERSION,
            $db,
            $table,
        ]);

        $key = md5($key);

        $this->redis->incr($key);
    }

    /**
     * 对整个数据的缓存失效主要采用对key incr
     * @param string $db
     * @param string $table
     */
    public function flushAll($db, $table)
    {
        $key = implode(':', [
            $this->prefix,
            self::KEY_SCHEMA_VERSION,
            $db,
            $table,
        ]);
        $key = md5($key);

        $this->redis->incr($key);
    }
}
