<?php
/**
 * Base Exception
 *
 * @author feng_li@ruijie.com.cn
 */

namespace Lethe\Lib\Bases;
use Lethe\Lib\Log;

class Error extends \Exception
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
        Log::error(json_encode($data), $data, 'error_sys');
    }
}
