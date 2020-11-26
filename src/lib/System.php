<?php
namespace Lethe\Lib;

/**
 * Created by PhpStorm.
 * User: hp
 * Date: 2018/6/5
 * Time: 14:58
 */
use app\services\Appconfig;
use app\services\user\User;
use app\models\User as UserModel;

class SystemSetting
{
    const MODULE_NAME = 'systemsetting';

    public static function getServerTime()
    {
        return date("Y-m-d H:i:s", time());
    }

    public static function getAdminPhone()
    {
        $userService = new User();
        $adminInfo = $userService->queryAllAdmin()['data'];

        return $adminInfo['phone'];
    }

    public static function setPasswordType($type)
    {
        Appconfig::setValue(
            self::MODULE_NAME,
            'password_type',
            $type
        );

        return true;
    }

    public static function getPasswordType()
    {
        if (!Appconfig::hasKey(self::MODULE_NAME, 'password_type')) {
        }

        $pwdType = Appconfig::getValue(
            self::MODULE_NAME,
            'password_type'
        );

        return '0' === $pwdType ? ['pwd_type'=> $pwdType, 'default' => UserModel::DEFAULT_PASSWORD] : ['pwd_type'=> $pwdType];
    }

    public static function setLogLevel($level)
    {
        Appconfig::setValue(
            self::MODULE_NAME,
            'loglevel',
            $level
        );

        return true;
    }

    public static function setSecurity($enforceHttps)
    {
        Appconfig::setValue(
            self::MODULE_NAME,
            'forcessl',
            filter_var($enforceHttps, FILTER_VALIDATE_BOOLEAN)
        );

        return true;
    }

    public static function setLanguage($lang)
    {
        Appconfig::setValue(
            self::MODULE_NAME,
            'lang',
            $lang
        );

        return true;
    }
}
