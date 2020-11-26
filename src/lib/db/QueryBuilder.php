<?php
namespace Lethe\Lib\DB;

use Lethe\Lib\Bases\Redis;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Lethe\Lib\Cache\RedisCache;
use Lethe\Lib\db\cache\RedisMeta;

class QueryBuilder extends Builder
{
    /**
     * @var Model
     */
    private $model;
    private function needCache()
    {
        if (!$this->model) {
            return false;
        }

        return $this->model->needCache();
    }

    private function needFlushCache()
    {
        // 如果没有设置 model,则认为不用处理缓存逻辑
        if (!$this->model) {
            return false;
        }

        return $this->model->needFlushCache();
    }

    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @return Cache
     */
    protected function getCache()
    {
        return new RedisCache(Redis::getInstance());
    }

    /**
     * @return Meta
     */
    protected function getMeta()
    {
        return new RedisMeta(Redis::getInstance());
    }
    public function get($columns = ['*'])
    {
        if (!$this->needCache()) {
            return parent::get($columns);
        }

        if (!$this->columns && self::hasRawColumn($columns)) {
            $this->columns = $columns;
        }
        //根据查询是否为复杂查询，简单查询采用不同的缓存方式

        if ($this->isSimple()) {
            return $this->getSimple();
        }
        return $this->getAwful();
    }

    /**
     * 是否含有表达式
     * @param $columns
     * @return bool
     */
    private static function hasRawColumn($columns)
    {
        if (!$columns) {
            return false;
        }
        foreach ($columns as $column) {
            if ($column instanceof Expression) {
                return true;
            }
        }

        return false;
    }

    /**
     * 获取复杂查询的结果集
     * @return array|static[]
     */
    private function getAwful()
    {
        $key = $this->buildAwfulCacheKey();
        $cache = $this->getCache();
        $result = $cache->get([$key]);
        if (\array_key_exists($key, $result)) {
            return collect($result[$key]);
        }
        $result = parent::get();
        $cache->set([
            $key => $result,
        ]);

        return $result;
    }

    /**
     * 生成复杂查询的cachekey
     * @return string
     */
    private function buildAwfulCacheKey()
    {
        return $this->buildTableCacheKey($this->toSql(), $this->getBindings());
    }

    /**
     * 判断当前查询是否未「复杂查询」，判断标准
     * 1. 含有 max, sum 等汇聚函数
     * 2. 包含 distinct 指令
     * 3. 包含分组
     * 4. 包含连表
     * 5. 包含联合
     * 6. 包含子查询
     * 7. 包含原生（raw）语句
     * 8. 包含排序
     *
     * 复杂查询使用表级缓存，命中率较低
     */
    private function isAwful()
    {
        if (self::hasRawColumn($this->columns)) {
            return true;
        }

        return $this->aggregate
        or $this->distinct
        or $this->groups
        or $this->joins
        or $this->orders
        or $this->unions
        or !$this->wheres
        or \array_key_exists('Exists', $this->wheres)
        or \array_key_exists('InSub', $this->wheres)
        or \array_key_exists('NotExists', $this->wheres)
        or \array_key_exists('NotInSub', $this->wheres)
        or \array_key_exists('Sub', $this->wheres)
        or \array_key_exists('raw', $this->wheres);
    }

    /**
     * 简单查询，只根据主键过滤结果集
     */
    private function getSimple()
    {
        $primaryKeyName = $this->model->primaryKey();
        //{ [110700201]=> string(32) "36c1cbba6661ba1ccf5011c8107c4719" }
        $cacheKeys = $this->buildCacheKeys();
        //交换数组中的键和值
        $keyId = array_flip($cacheKeys);

        $cache = $this->getCache();
        $cachedRows = $cache->get(array_values($cacheKeys));
        foreach ($cachedRows as $key => $row) {
            unset($cacheKeys[$keyId[$key]]);
        }
        $cachedRows = array_filter(array_values($cachedRows), function ($row) {
            return $row !== [];
        });
        $missedIds = array_keys($cacheKeys);
        if (!$missedIds) {
            return collect($cachedRows);
        }
        $originWheres = $this->wheres;
        $originWhereBindings = $this->bindings;
        $originColumns = $this->columns;
        $this->wheres = [];
        // $this->bindings = array();
        $this->bindings['where'] = [];
        $this->whereIn($primaryKeyName, $missedIds);
        $this->columns = null;
        $missedRows = array_fill_keys($missedIds, []);
        foreach (parent::get() as $row) {
            $missedRows[$row->$primaryKeyName] = $row;
        }

        $this->wheres = $originWheres;
        //$this->bindings = $originWhereBindings;
        $this->bindings['where'] = $originWhereBindings;
        $this->columns = $originColumns;
        $toCachRows = [];
        $toCachIds = array_keys($missedRows);
        $toCachKeys = $this->buildRowCacheKey($toCachIds);
        foreach ($missedRows as $id => $row) {
            $toCachRows[$toCachKeys[$id]] = $row;
        }
        if ($toCachRows) {
            $cache->set($toCachRows);
        }
        $missedRows = array_filter(array_values($missedRows), function ($row) {
            return $row !== [];
        });
        return collect(array_merge($cachedRows, $missedRows));
    }

    private function buildCacheKeys()
    {
        $where = current($this->wheres);
        if ($where['type'] == 'In') {
            $ids = $where['values'];
        } else {
            $ids = [$where['value']];
        }

        $cacheKeys = $this->buildRowCacheKey($ids);

        return $cacheKeys;
    }

    /**
     * 「简单查询」就是只根据主键过滤结果集的查询，有以下两种形式：
     * 1. select * from foo where id = 1;
     * 2. select * from foo where id in (1, 2, 3);
     */
    private function isSimple()
    {
        if ($this->isAwful()) {
            return false;
        }

        if (!$this->wheres) {
            return false;
        }

        if (\count($this->wheres) > 1) {
            return false;
        }

        $where = current($this->wheres);

        if ($where['type'] === 'Nested') {
            return false;
        }

        $id = $this->model->primaryKey();
        $tableId = $this->model->table() . '.' . $this->model->primaryKey();
        if (!\in_array($where['column'], [$id, $tableId])) {
            return false;
        }

        if ($where['type'] === 'In') {
            return true;
        }

        if ($where['type'] === 'Basic') {
            if ($where['operator'] === '=') {
                return true;
            }
        }

        return false;
    }

    public function delete($id = null)
    {
        if ($this->needFlushCache()) {
            // 清空表级缓存
            $meta = $this->getMeta();
            $meta->flush($this->db(), $this->model->table());
            $this->flushAffectingRowCache();
        }
        return parent::delete($id);
    }

    /**
     * 查找受影响的 ID，清空相关行级缓存
     */
    private function flushAffectingRowCache()
    {
        $keyName = $this->model->primaryKey();
        $toDeleteRows = parent::get();
        $ids = [];
        foreach ($toDeleteRows as $row) {
            $ids[] = $row[$keyName];
        }
        if ($ids) {
            $cacheKeys = $this->buildRowCacheKey($ids);
            $cache = $this->getCache();
            $cache->del(array_values($cacheKeys));
        }
    }

    public function update(array $values)
    {
        if ($this->needFlushCache()) {
            // 清空表级缓存
            $meta = $this->getMeta();
            $meta->flush($this->db(), $this->model->table());

            $this->flushAffectingRowCache();
        }
        return parent::update($values);
    }

    public function insert(array $values)
    {
        if ($this->needFlushCache()) {
            // 清空表级缓存
            $meta = $this->getMeta();
            $meta->flush($this->db(), $this->model->table());

            if (! \is_array(reset($values))) {
                $values = [$values];
            }
            $toClearIds = [];
            foreach ($values as $value) {
                $toClearIds[] = $value[$this->model->primaryKey()];
            }
            $toClearKeys = $this->buildRowCacheKey($toClearIds);
            $this->getCache()->del(array_values($toClearKeys));
        }

        return parent::insert($values);
    }

    public function insertGetId(array $values, $sequence = null)
    {
        if ($this->needFlushCache()) {
            // 清空表级缓存
            $meta = $this->getMeta();
            $meta->flush($this->db(), $this->model->table());
        }

        $id = parent::insertGetId($values, $sequence);

        if ($this->needFlushCache()) {
            $keys = $this->buildRowCacheKey([$id]);
            $key = $keys[$id];
            $this->getCache()->del([$key]);
        }

        return $id;
    }

    /**
     * 获取当前查询所用的数据库名称
     */
    private function db()
    {
        return $this->connection->getDatabaseName();
    }

    /**
     * 构造行级缓存索引
     * @param $keyValues
     * @return array
     */
    private function buildRowCacheKey($keyValues)
    {
        $meta = $this->getMeta();
        $prefix = $meta->prefix($this->db(), $this->model->table());

        $keys = [];
        foreach ($keyValues as $keyValue) {
            $keys[$keyValue] = md5($prefix . ':' . $keyValue);
        }

        return $keys;
    }

    /**
     * 构造表级缓存索引key
     * @param $sql
     * @param $bindings
     * @return string
     */
    private function buildTableCacheKey($sql, $bindings)
    {
        $meta = $this->getMeta();
        $parts = [
            $meta->prefix($this->db(), $this->model->table(), true),
            $sql,
            json_encode($bindings),
        ];

        return md5(implode(':', $parts));
    }

    /**
     * 返回查询对应的缓存 key
     */
    public function key()
    {
        if ($this->isSimple()) {
            return $this->buildCacheKeys();
        } elseif ($this->isAwful()) {
            return $this->buildAwfulCacheKey();
        }
        return $this->buildAwfulCacheKey();
    }

    /**
     * 过期当前表所有缓存
     */
    public function flush()
    {
        $this->getMeta()->flushAll($this->db(), $this->model->table());
    }
}
