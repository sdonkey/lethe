<?php
namespace Lethe\Lib;

/**
 * session 管理类,封装了对session的操作
 * @author wangjr129@163.com
 */

class Session
{
    protected static $prefix = '';
    protected static $init   = null;

    /**
     * 设置或者获取session作用域（前缀）
     * @param string $prefix
     * @return string|void
     */
    public static function prefix($prefix = '')
    {
        if (empty($prefix) && null !== $prefix) {
            return self::$prefix;
        }
        self::$prefix = $prefix;
    }

    /**
     * session初始化
     * @param array $config
     * @throws \Exception
     * @return void
     */
    public static function init(array $config = [])
    {
        if (empty($config)) {
            $config = Config::get('session');
        }
        $isDoStart = false;
        if (isset($config['use_trans_sid'])) {
            ini_set('session.use_trans_sid', $config['use_trans_sid'] ? 1 : 0);
        }

        // 启动session
        if (!empty($config['auto_start']) && PHP_SESSION_ACTIVE != session_status()) {
            ini_set('session.auto_start', 0);
            $isDoStart = true;
        }

        if (isset($config['prefix']) && (self::$prefix === '' || self::$prefix === null)) {
            self::$prefix = $config['prefix'];
        }
        if (isset($config['var_session_id']) && isset($_REQUEST[$config['var_session_id']])) {
            session_id($_REQUEST[$config['var_session_id']]);
        } elseif (isset($config['id']) && !empty($config['id'])) {
            session_id($config['id']);
        }
        if (isset($config['name'])) {
            session_name($config['name']);
        }
        if (isset($config['path'])) {
            session_save_path($config['path']);
        }
        if (isset($config['domain'])) {
            ini_set('session.cookie_domain', $config['domain']);
        }
        if (isset($config['expire'])) {
            ini_set('session.gc_maxlifetime', $config['expire']);
            ini_set('session.cookie_lifetime', $config['expire']);
        }

        if (isset($config['secure'])) {
            ini_set('session.cookie_secure', $config['secure']);
        }
        if (isset($config['httponly'])) {
            ini_set('session.cookie_httponly', $config['httponly']);
        }
        if (isset($config['use_cookies'])) {
            ini_set('session.use_cookies', $config['use_cookies'] ? 1 : 0);
        }
        if (isset($config['cache_limiter'])) {
            session_cache_limiter($config['cache_limiter']);
        }
        if (isset($config['cache_expire'])) {
            session_cache_expire($config['cache_expire']);
        }
        if (!empty($config['type'])) {
            // 读取session驱动
            $class = false !== strpos($config['type'], '_') ? $config['type'] : '\session\driver\\'.ucwords($config['type']).'Driver';
            // 检查驱动类
            if (!class_exists($class) || !session_set_save_handler(new $class($config))) {
                //throw new Exception('error session handler:' . $class, $class);
                throw new Exception('error session handler:' . $class);
            }
        }
        if ($isDoStart) {
            session_start();
            self::$init = true;
        } else {
            self::$init = false;
        }
    }

    /**
     * session自动启动或者初始化
     * @return void
     */
    public static function boot()
    {
        if (null === self::$init) {
            self::init();
        } elseif (false === self::$init) {
            if (PHP_SESSION_ACTIVE != session_status()) {
                session_start();
            }
            self::$init = true;
        }
    }

    /**
     * session设置
     * @param string        $name session名称
     * @param mixed         $value session值
     * @param string|null   $prefix 作用域（前缀）
     * @return void
     */
    public static function set($name, $value = '', $prefix = null)
    {
        empty(self::$init) && self::boot();

        $prefix = null !== $prefix ? $prefix : self::$prefix;
        if (strpos($name, '.')) {
            // 二维数组赋值
            list($name1, $name2) = explode('.', $name);
            if ($prefix) {
                $_SESSION[$prefix][$name1][$name2] = $value;
            } else {
                $_SESSION[$name1][$name2] = $value;
            }
        } elseif ($prefix) {
            $_SESSION[$prefix][$name] = $value;
        } else {
            $_SESSION[$name] = $value;
        }
    }

    /**
     * 获取session id.
     *
     * @return string
     */
    public static function getSessionId()
    {
        empty(self::$init) && self::boot();
        return session_id();
    }

    /**
     * session获取
     * @param string        $name session名称
     * @param string|null   $prefix 作用域（前缀）
     * @return mixed
     */
    public static function get($name = '', $prefix = null)
    {
        empty(self::$init) && self::boot();
        $prefix = null !== $prefix ? $prefix : self::$prefix;
        if ('' == $name) {
            // 获取全部的session
            $value = $prefix ? (!empty($_SESSION[$prefix]) ? $_SESSION[$prefix] : []) : $_SESSION;
        } elseif ($prefix) {
            // 获取session
            if (strpos($name, '.')) {
                list($name1, $name2) = explode('.', $name);
                $value               = $_SESSION[$prefix][$name1][$name2] ?? null;
            } else {
                $value = $_SESSION[$prefix][$name] ?? null;
            }
        } else {
            if (strpos($name, '.')) {
                list($name1, $name2) = explode('.', $name);
                $value               = $_SESSION[$name1][$name2] ?? null;
            } else {
                $value = $_SESSION[$name] ?? null;
            }
        }

        return $value;
    }

    /**
     * session获取并删除
     * @param string        $name session名称
     * @param string|null   $prefix 作用域（前缀）
     * @return mixed
     */
    public static function pull($name, $prefix = null)
    {
        $result = self::get($name, $prefix);
        if ($result) {
            self::delete($name, $prefix);
            return $result;
        }
    }

    /**
     * 删除session数据
     * @param string|array  $name session名称
     * @param string|null   $prefix 作用域（前缀）
     * @return void
     */
    public static function delete($name, $prefix = null)
    {
        empty(self::$init) && self::boot();
        $prefix = null !== $prefix ? $prefix : self::$prefix;
        if (is_array($name)) {
            foreach ($name as $key) {
                self::delete($key, $prefix);
            }
        } elseif (strpos($name, '.')) {
            list($name1, $name2) = explode('.', $name);
            if ($prefix) {
                unset($_SESSION[$prefix][$name1][$name2]);
            } else {
                unset($_SESSION[$name1][$name2]);
            }
        } else {
            if ($prefix) {
                unset($_SESSION[$prefix][$name]);
            } else {
                unset($_SESSION[$name]);
            }
        }
    }

    /**
     * 清空session数据
     * @param string|null   $prefix 作用域（前缀）
     * @return void
     */
    public static function clear($prefix = null)
    {
        empty(self::$init) && self::boot();
        $prefix = null !== $prefix ? $prefix : self::$prefix;
        if ($prefix) {
            unset($_SESSION[$prefix]);
        } else {
            $_SESSION = [];
        }
    }

    /**
     * 判断session数据
     * @param string        $name session名称
     * @param string|null   $prefix
     * @return bool
     */
    public static function has($name, $prefix = null)
    {
        empty(self::$init) && self::boot();
        $prefix = null !== $prefix ? $prefix : self::$prefix;
        if (strpos($name, '.')) {
            // 支持数组
            list($name1, $name2) = explode('.', $name);
            return $prefix ? isset($_SESSION[$prefix][$name1][$name2]) : isset($_SESSION[$name1][$name2]);
        }
        return $prefix ? isset($_SESSION[$prefix][$name]) : isset($_SESSION[$name]);
    }

    /**
     * 添加数据到一个session数组
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public static function push($key, $value)
    {
        $array = self::get($key);
        if (null === $array) {
            $array = [];
        }
        $array[] = $value;
        self::set($key, $array);
    }

    /**
     * 启动session
     * @return void
     */
    public static function start()
    {
        session_start();
        self::$init = true;
    }

    /**
     * 销毁session
     * @return void
     */
    public static function destroy()
    {
        if (!empty($_SESSION)) {
            $_SESSION = [];
        }
        session_unset();
        session_destroy();
        self::$init = null;
    }

    /**
     * 重新生成session_id
     * @param bool $delete 是否删除关联会话文件
     * @return void
     */
    public static function regenerate($delete = false)
    {
        if (isset($_SESSION['user_id'])) {
            session_regenerate_id($delete);
        }
    }

    /**
     * 暂停session
     * @return void
     */
    public static function pause()
    {
        // 暂停session
        session_write_close();
        self::$init = false;
    }
}
