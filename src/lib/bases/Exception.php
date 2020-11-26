<?php
/**
 * Base Exception
 *
 * @author wuqiying@ruijie.com.cn
 */
namespace Lethe\Lib\Bases;

use app\exceptions\CmxException;
use \app\exceptions\handler\Response;
use \app\exceptions\code\System;
use \app\exceptions\handler\Log;

class Exception
{
    /**
     * @param object $e
     */
    public function handler($e)
    {
        $type = null;
        if ($e instanceof \ParseError) {
            ($type = 'parse');
        } elseif ($e instanceof \Error) {
            ($type = 'error');
        } elseif ($e instanceof \Yaf_Exception) {
            ($type = 'yaf');
        } elseif ($e instanceof \PDOException) {
            ($type = 'database');
        } elseif ($e instanceof \MemcachedException) {
            ($type = 'memcached');
        } elseif ($e instanceof \RedisException) {
            ($type = 'redis');
        } elseif ($e instanceof \MongoDB\Driver\Exception\Exception) {
            ($type = 'mongodb');
        } elseif ($e instanceof CmxException) {
            ($type = 'cmx');
        }

        switch ($type) {
            case 'parse':
            case 'error':
                $this->error($e);
                break;
                //break;
            case 'yaf':
                $this->yaf($e);
                break;
            case 'database':
                $this->database($e);
                break;
            case 'redis':
            case 'memcached':
            case 'mongodb':
                $this->cache($e, $type);
                break;
            case 'cmx':
                $this->cmx($e, $type);
                break;
            default:
                $this->application($e);
                break;
        }
    }
    /**
     * @param Yaf_Exception $e
     */
    private function yaf(\Yaf\Exception $e)
    {
        if ($e->getCode() === \YAF\ERR\NOTFOUND\CONTROLLER) {
            header('HTTP/1.0 404 Not Found');
        }
        (new Response())->system(System::YAF, $e->getMessage());
        (new Log())->system($e);
    }
    /**
     * @param PDOException $e
     */
    private function database(\PDOException $e)
    {
        (new Response())->system(System::DATABASE, $e->getMessage());
        (new Log())->system($e);
    }
    /**
     * @param Exception $e
     * @param string    $type
     */
    private function cache(\Exception $e, $type)
    {
        (new Response())->system(\constant('System::'.strtoupper($type)), $e->getMessage());
        (new Log())->system($e);
    }
    /**
     * @param Exception $e
     */
    private function application(\Exception $e)
    {
        (new Response())->application($e->getCode(), $e->getMessage());
        (new Log())->application($e);
    }

    private function cmx(CmxException $e)
    {
        (new Response())->application($e->getCode(), $e->getMessage());
        (new Log())->cmxException($e);
    }

    private function error(\Error $e){
        (new Response())->application($e->getCode(), $e->getMessage());
    }
}
