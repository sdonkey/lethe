<?php
namespace Lethe\Lib;

/**
 * 返回响应类
 * @author: wjr<wangjr129@163.com>
 * @Date: 2017/12/18 16:55
 */

class Response
{
    /**
     * 渲染json输出
     * @param $code
     * @param $msg
     * @param array $data
     * @throws Exception
     */
    public static function renderJson($code, $msg, $data = [])
    {
        header('Content-Type: application/json;charset=utf-8');
        $response = [];
        $response['code'] = $code;
        $response['status'] = $code ? 'error' : 'success';
        $response['message'] = $msg;
        $response['logid'] = Util::makeUniqUuid();
        $response['data'] = $data;
        /**
         * $GLOBALS['request_body'] = file_get_contents('php://input');
         * $GLOBALS['request_time'] = microtime(true);
         * $GLOBALS['start_memory'] = memory_get_usage();
         */

        if (isset($_GET['debug']) && $_GET['debug'] =='f27f94c9e07c725c') {
            $response['debug']['sql_debug'] = self::sql();
            $use_time = (microtime(true)-$GLOBALS['request_time'])*1000;
            $use_memory = (memory_get_usage()-$GLOBALS['start_memory'])/1024;
            $response['debug']['run_time'] = $use_time.'ms';
            $response['debug']['memory_use'] = $use_memory.'Byte';
        }

        PluginManager::trigger(
            'request_log',
            '==========request_body============='
            .PHP_EOL
            .$GLOBALS['request_body'].
            '==============response_body=================='
            .PHP_EOL
            .print_r($response, true)
        );
        exit(json_encode($response, JSON_UNESCAPED_UNICODE));
    }

    public static function sql()
    {
        $sqlMap = \Illuminate\Database\Capsule\Manager::getQueryLog();
        $sqlLog = [];
        foreach ($sqlMap as $i => $binding) {
            $query = $binding['query'];
            foreach ($binding['bindings'] as &$val) {
                if (is_string($val)) {
                    $val = "'".$val."'";
                }
            }
            $query = str_replace('"', '', $query);
            $query = str_replace(['%', '?'], ['%%', '%s'], $query);
            $query = vsprintf($query, $binding['bindings']);

            $sqlLog[$i] = $query.'     Time:['.$binding['time']."ms]";
        }
        return $sqlLog;
    }
}
