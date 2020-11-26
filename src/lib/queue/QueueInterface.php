<?php
/**
 * Queue Interface
 *
 * @author wuqiying@ruijie.com.cn
 */
namespace Lethe\Lib\Queue;

interface QueueInterface
{
    /**
     * @param string $key
     * @param mixed  $val
     * @return mixed
     */
    public function push($key, $val);
    /**
     * @param string $key
     * @return mixed
     */
    public function pop($key);
}
