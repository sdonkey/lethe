<?php
namespace Lethe\Lib;

/**
 * Created by PhpStorm.
 * User: feng_li@ruijie.com.cn
 * Date: 2018/11/28
 * Time: 14:00
 */

class PluginManager
{
    /**
     * 监听已注册的插件
     *
     * @access private
     * @var array
     */
    private static $listeners = [];

    /**
     * 注册需要监听的插件方法（钩子）
     *
     * @param string $hook
     * @param object $reference
     * @param string $method
     */
    public static function register($hook, $reference, $method = 'run')
    {
        //获取插件要实现的方法
        $key = $reference.'->'.$method;
        //将插件的引用连同方法push进监听数组中
        self::$listeners[$hook][$key] = [$reference, $method];
        #此处做些日志记录方面的东西
    }
    /**
     * 触发一个钩子
     *
     * @param string $hook 钩子的名称
     * @param mixed $data 钩子的入参
     * @return mixed
     */
    public static function trigger($hook, $data = '')
    {
        $result = '';
        //查看要实现的钩子，是否在监听数组之中
        if (isset(self::$listeners[$hook]) && is_array(self::$listeners[$hook]) && count(self::$listeners[$hook]) > 0) {
            // 循环调用开始
            foreach (self::$listeners[$hook] as $listener) {
                // 取出插件对象的引用和方法
                $class = $listener[0];
                $method = $listener[1];
                $result .= call_user_func_array([$class, $method], ['data'=>$data]);
            }
        }
        #此处做些日志记录方面的东西
        return $result;
    }
}
