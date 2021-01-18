<?php
/* **********************************************************
 *
 * @author: wangjianrong<wangjr129@163.com>
 * @Date: 2021/1/15 3:29 下午
 * @copyright Copyright 2018-2023 © wangjr129@163.com All rights reserved.
 * *********************************************************** */

namespace Lethe\Lib;


class Exception extends \Exception
{
    /**
     * @param $code
     * @param $message
     * @param $file
     * @param $line
     */
    public function handler($code, $message, $file, $line)
    {
        $data = [
            'code' => $code,
            'message' => $message,
            'file' => $file,
            'line' => $line
        ];
        \Log::error(json_encode($data), $data, 'error_sys');
    }
}
