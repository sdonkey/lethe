<?php
namespace Lethe\Lib;

/**
 * Created by PhpStorm.
 * User: feng_li@ruijie.com.cn
 * Date: 2018/5/21
 * Time: 13:46
 */

class ModelFactory
{
    public static $creditsModel = null;
    public static $creditsSourceModel = null;
    public static $medalDetailModel = null;
    public static $medalSourceModel = null;
    public static $msgModel = null;
    public static $presentSource = null;

    public static function createModel($model_name)
    {
        $object = null;
        switch ($model_name) {
            case 'Msg':
                if (!self::$msgModel) {
                    self::$msgModel = new \app\models\Msg();
                }
                $object = self::$msgModel;
                break;
            case 'Credits':
                if (!self::$creditsModel) {
                    self::$creditsModel = new \app\models\Credits();
                }
                $object = self::$creditsModel;
                break;
            case 'CreditsSource':
                if (!self::$creditsSourceModel) {
                    self::$creditsSourceModel = new \app\models\CreditsSource();
                }
                $object = self::$creditsSourceModel;
                break;
            case 'MedalDetail':
                if (!self::$medalDetailModel) {
                    self::$medalDetailModel = new \app\models\MedalDetail();
                }
                $object = self::$medalDetailModel;
                break;
            case 'MedalSource':
                if (!self::$medalSourceModel) {
                    self::$medalSourceModel = new \app\models\MedalSource();
                }
                $object = self::$medalSourceModel;
                break;
            case 'PresentSource':
                if (!self::$presentSource) {
                    self::$presentSource = new \app\models\PresentSource();
                }
                $object = self::$presentSource;
                break;
        }
        return $object;
    }
}
