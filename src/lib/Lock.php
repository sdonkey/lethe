<?php
namespace Lethe\Lib;

/* **********************************************************
 * redis锁
 * @author: wangjianrong<wangjr129@163.com>
 * @Date: 2020-04-30 10:47
 * @copyright Copyright 2018-2023 © wangjr129@163.com All rights reserved.
 * *********************************************************** */

use Lethe\Lib\Bases\Redis;

class Lock
{
    /**
     * @var string
     */
    protected $useCache = '';

    protected $prefix = '';

    public function __construct()
    {
        $config = Config::get('redis');
        $this->useCache =  Redis::getInstance($config['terminal_db']);
        $this->prefix = 'lock_';
    }

    protected $expire = 100;

    protected $lockCache = [];

    /**
     * 设置锁的过期时间
     *
     * @param int $expire
     *
     * @return $this
     */
    public function setExpire($expire = 100)
    {
        $this->expire = $expire;
        return $this;
    }

    /**
     * 组装key
     *
     * @param string $key 要上的锁的key
     *
     * @return string
     */
    protected function getKey($key)
    {
        return $this->prefix.$key;
    }

    /**
     * 上锁
     *
     * @param string $key 要上的锁的key
     * @param mixed $wouldBlock
     *
     * @return mixed
     */
    public function lock($key, $wouldBlock = true)
    {
        if (empty($key)) {
            return false;
        }
        $key = $this->getKey($key);

        if (isset($this->lockCache[$key])
            && $this->lockCache[$key] == $this->useCache->get($key)
        ) {
            return true;
        }
        $value = Util::makeUuid();
        if ($this->useCache->set(
            $key,
            $value,
            ['nx', 'ex' => $this->expire]
        )
        ) {
            $this->lockCache[$key] = $value;
            return true;
        }

        //非堵塞模式
        if (!$wouldBlock) {
            return false;
        }

        //堵塞模式
        do {
            usleep(200);
        } while (!$this->useCache->set(
            $key,
            $value,
            ['nx', 'ex' => $this->expire]
        ));

        $this->lockCache[$key] = $value;
        return true;
    }

    /**
     * 解锁
     *
     * @param string $key
     *
     * @return void
     */
    public function unlock($key)
    {
        $key = $this->getKey($key);

        if (isset($this->lockCache[$key])
            && $this->lockCache[$key] == $this->useCache->get($key)
        ) {
            $this->useCache->delete($key);
            $this->lockCache[$key] = null;
            unset($this->lockCache[$key]);
        }
    }

    /**
     * 定义析构函数
     */
    public function __destruct()
    {
        foreach ($this->lockCache as $key => $isMyLock) {
            if ($isMyLock == $this->useCache->get($key)) {
                $this->useCache->delete($key);
            }
            $this->lockCache[$key] = null;
            unset($this->lockCache[$key]);
        }
    }
}
