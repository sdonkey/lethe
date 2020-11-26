<?php
/**
 * Created by PhpStorm.
 * User: chenchun
 * Date: 2020/6/23
 * Time: 13:20
 */
namespace Lethe\Lib\DB;

use Illuminate\Support\Facades\Facade;

class LSchema extends Facade
{
    public static $db = null;

    public static function setDb($manager)
    {
        self::$db = $manager;
    }

    public static function connection($name)
    {
        return self::$db->connection($name)->getSchemaBuilder();
        //return static::$app['db']->connection($name)->getSchemaBuilder();
    }
    protected static function getFacadeAccessor()
    {
        return self::$db->connection()->getSchemaBuilder();
        //return static::$app['db']->connection()->getSchemaBuilder();
    }
}
