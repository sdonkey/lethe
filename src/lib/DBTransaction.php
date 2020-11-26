<?php
namespace Lethe\Lib;

/**
 * Created by PhpStorm.
 * User: feng_li@ruijie.com.cn
 * Date: 2018/3/15
 * Time: 13:46
 */

use \Illuminate\Database\Eloquent\Model;

class DBTransaction extends Model
{
    public static $obj;

    public static function getInstance()
    {
        if (!self::$obj) {
            self::$obj = new self();
        }
        return self::$obj;
    }

    public static function begin()
    {
        self::getInstance()->getConnection()->beginTransaction();
    }

    public static function commit()
    {
        self::getInstance()->getConnection()->commit();
    }

    public static function rollback()
    {
        self::getInstance()->getConnection()->rollBack();
    }
}
