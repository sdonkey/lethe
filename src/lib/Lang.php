<?php
namespace Lethe\Lib;

/**
 * Created by PhpStorm.
 * User: feng_li@ruijie.com.cn
 * Date: 2018/11/28
 * Time: 14:00
 */

class Lang
{
    const TOKEN = '3iSIu9SvCtM%l(8Yo@cynCYi#yn^eQm&';

    const OWNCLOUD_LANG_MAP = [
        'zh-cn' => 'zh_CN',
        'en-us' => 'en',
        'tr-tr' => 'tr',
    ];

    /**
     * 设置语言类型
     * @param $lang_value
     * @return mixed
     */
    public static function setLangConf($lang_value)
    {
        $lang_conf = AppconfigModel::single()->getValue('lang', 'lang_conf');
        if (empty($lang_conf)) {
            $data = [
                'module' => 'lang',
                'configkey' => 'lang_conf',
                'configvalue' => $lang_value
            ];
            AppconfigModel::single()->create($data);
        } else {
            AppconfigModel::query()->where('module', 'lang')
                ->where('configkey', 'lang_conf')->update(['configvalue' => $lang_value]);
        }

        if (!empty($lang_value)) {
            Request::post(self::getOwnUrl($lang_value));
        }

        $lang_list = self::getContent($lang_value);
        Cache::set('lang_list', $lang_list, 7*24*3600);
        return $lang_value;
    }

    public static function getLangConf()
    {
        $lang_conf = AppconfigModel::single()->getValue('lang', 'lang_conf');
        $lang_conf = empty($lang_conf) ? 'zh-cn' : $lang_conf['configvalue'];
        return $lang_conf;
    }

    /**
     * 获取当前语言
     * @return mixed
     */
    public static function getLangList()
    {
        $lang_list = Cache::get('lang_list');
        //TODO
        $lang_list = null;
        if (empty($lang_list)) {
            $lang_conf = AppconfigModel::single()->getValue('lang', 'lang_conf');
            $lang_conf = empty($lang_conf['configvalue']) ? 'zh-cn' : $lang_conf['configvalue'];
            $lang_list = self::getContent($lang_conf);
            Cache::set('lang_list', $lang_list, 7*24*3600);
        }
        return $lang_list;
    }

    public static function get($key, $module = 'common')
    {
        $lang_list = self::getLangList();
        return $lang_list[$module][$key];
    }

    private static function getContent($lang_value)
    {
        $lang_list = [];
        $files = scandir(LANG_PATH . '/' . $lang_value);
        foreach ($files as $file) {
            $file_info = explode(".", $file);
            if ($file_info[1] == 'php') {
                $lang_list[$file_info[0]] = include LANG_PATH . '/' . $lang_value . '/'.$file;
            }
        }
        return $lang_list;
    }

    /**
     * 获取owncloud修改语言url及参数组合
     *
     * @param string $lang
     * @return string
     */
    private static function getOwnUrl($lang)
    {
        $time = time();
        $sign = md5($time . self::TOKEN);
        $lang = self::OWNCLOUD_LANG_MAP[$lang];
        $config = \Config::get('application');
        $domain = $config['application']['owncloud']['domain'];
        $url = $config['application']['owncloud']['setLang'];
        return 'http://' . $domain . $url . "?time=$time&sign=$sign&lang=$lang";
    }
}
