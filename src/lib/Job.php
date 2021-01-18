<?php
namespace Lethe\Lib;

/**
 * Job
 *
 * @author wuqiying@ruijie.com.cn
 */

class Job
{
    /**
     * @var string
     */
    public static $queueName = 'queue_jobs';
    /**
     * @var array
     */
    public static $events;
    /**
     * @param mixed $key
     * @param mixed $obj
     */
    public static function addEvent($key, $obj)
    {
        self::$events[$key] = $obj;
    }

    public static function run()
    {
        $res     = self::dequeue();
        if (!empty($res)) {
            $event   = $res['event'];

            $data    = $res['data'];
            $handler = null;
            if (isset(self::$events[$event])) {
                $handler = self::$events[$event];
            }
            if ($handler) {
                call_user_func_array($handler, $data);
            }
        }
    }

    public static function dequeue()
    {
        $key = self::getQueueName();
        $value = Queue::pop($key);
        if (!empty($value)) {
            return json_decode($value[1],true);
        }
    }
    /**
     * @param mixed $event
     * @param mixed $data
     * @return bool
     */
    public static function enqueue($event, $data)
    {
        $key = self::getQueueName();
        $val = [
            'event' => $event,
            'data'  => $data
        ];
        return Queue::push($key, json_encode($val));
    }
    /**
     * @return string
     */
    public static function getQueueName()
    {
        return self::$queueName;
    }
    /**
     * @param string $queueName
     */
    public static function setQueueName($queueName)
    {
        self::$queueName = $queueName;
    }
}
