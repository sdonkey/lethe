<?php
/**
 * Base Event
 *
 * @author zhangli@ruijie.com.cn
 */
namespace Lethe\Lib\Bases;

use Evenement\EventEmitter;

class Event
{
    /**
     * @return EventEmitter
     */
    public static function getInstance()
    {
        $instance = \Yaf\Registry::get('EVENT_INSTANCE');
        if (!$instance) {
            $instance = new EventEmitter();
            \Yaf\Registry::set('EVENT_INSTANCE', $instance);
        }

        return $instance;
    }
}
