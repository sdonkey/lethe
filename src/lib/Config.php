<?php
namespace Lethe\Lib;

/**
 * Created by PhpStorm.
 * User: feng_li@ruijie.com.cn
 * Date: 2018/3/15
 * Time: 13:46
 */

class Config
{
    public static function get($key, $environ = '')
    {
        $file = CONF_PATH.'/' . $key . '.ini';
        $config = new \Yaf\Config\Ini($file);

        if (empty($environ)) {
            return $config->get(\YAF\ENVIRON)->toArray();
        }
        return $config->get($environ)->toArray();
    }
}
