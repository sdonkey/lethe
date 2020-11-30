<?php
namespace Lethe\Lib\Bases;

use Illuminate\Database\Eloquent\Model;
use Lethe\Lib\DB\QueryBuilder;


class ModelBase extends Model
{
    public $timestamps = false;
    protected static $disableReadCache = false;
    protected $needCache = false;
    public static function single()
    {
        static $_instance = [];
        $class_name = static::class;
        if (!isset($_instance[$class_name])) {
            $instance = $_instance[$class_name] = new static();
            return $instance;
        }
        return $_instance[$class_name];
    }

    public function insertEntity($data)
    {
        return self::query()->insert($data);
    }

    /**
     * 调用此方法需要在对应的model中添加protected $primaryKey字段，若数据库中该字段为id可忽略
     * @param $data
     * @return mixed
     */
    public function createGetId($data)
    {
        return self::insertGetId($data, $this->primaryKey);
    }
    public function getEntityById($id)
    {
        if (empty($this->primaryKey())) {
            return false;
        }
        return self::where($this->primaryKey(), $id)->first();
    }
    public function needCache()
    {
        return $this->needCache && !self::$disableReadCache;
    }
    /**
     * 判断更新数据库的时候是否需要更新缓存
     */
    public function needFlushCache()
    {
        return $this->needCache;
    }

    public function primaryKey()
    {
        return $this->primaryKey;
    }

    public function table()
    {
        return $this->table;
    }

    protected function newBaseQueryBuilder()
    {
        $conn = $this->getConnection();

        $grammar = $conn->getQueryGrammar();
        $queryBuilder = new QueryBuilder(
            $conn,
            $grammar,
            $conn->getPostProcessor()
        );
        $queryBuilder->setModel($this);

        return $queryBuilder;
    }

    /**
     * 关闭查询数据库的时候读取缓存的逻辑
     */
    public static function disableReadCache()
    {
        self::$disableReadCache = true;
    }
}
