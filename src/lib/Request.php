<?php
namespace Lethe\Lib;

/**
 * 请求类.
 * @author yuanjiwei@ruijie.com.cn
 * @version V1.0.0
 */
use GuzzleHttp\Client;
use app\exceptions\CmxException;
use app\exceptions\code\Owncloud as OwncloudException;

class Request
{
    const POST_TYPE = [
        'json' => 'json',
        'multipart' => 'multipart'
    ];

    /**
     * Make & Send request.
     * @param string $method
     * @param string $url
     * @param array $params
     * @param mixed $options
     * @param mixed $header
     * @return array
     */
    public static function post($url, $options = [], $header = [])
    {
        // make and send request
        $client = new Client();

        $type = $options['type'];
        $data = $options['data'];
        $timeout = $options['timeout'] ?? 10;
        try {
            $res = $client->request('POST', $url, [
                'headers' => $header,
                'timeout' => $timeout,
                $type => $data,
            ]);

            // response success to return.
            if (200 === $res->getStatusCode()) {
                return $res->getBody()->getContents();
            }

            throw new CmxException(OwncloudException::HTTP_RESPONSE_ERROR);
        } catch (\Exception $e) {
            $message = [
                'message' => $e->getMessage(),
                'uid' => \Session::get('user_id'),
                'url' => $url,
                'params' => $data,
                'sid' => \Session::getSessionId(),
            ];
            // 记录错误信息
            Log::error(json_encode($message), [], 'request');
            return false;
        }
    }

    public static function owncloudRestful($url, $json, $settings = [])
    {
        $sid = !isset($settings['sid']) && empty($settings['sid']) ? Session::getSessionId() : $settings['sid'];
        $baseUrl = self::getUrl();
        $url = $baseUrl . ltrim($url, '/') . '?format=json&HTTP_SID='.$sid;
        $timeout = isset($settings['timeout']) && !empty($settings['timeout']) ? $settings['timeout'] : 10;
        $options = [
            'type' => self::POST_TYPE['json'],
            'data' => $json,
            'timeout' => $timeout,
        ];
        $return = self::post($url, $options);
        if (empty($return)) {
            return false;
        }

        $return = trim($return, '﻿ ');
        $content = json_decode($return, true);
        $data = $content['ocs'];
        if (100 == $data['meta']['statuscode']) {
            return $data['data'];
        }
        return false;
    }

    public static function uploadFileToOwncloud($url, $data, $file, $settings = [])
    {
        $sid = !isset($settings['sid']) && empty($settings['sid']) ? Session::getSessionId() : $settings['sid'];

        $url = 'https://127.0.0.1:9310/owncloud/index.php/'. ltrim($url, '/');
        //$url = '/owncloud/index.php/'. ltrim($url, '/');
        $timeout = isset($settings['timeout']) && !empty($settings['timeout']) ? $settings['timeout'] : 10;
        $curl = curl_init();
        $data['files[]'] = new CURLFILE($file);
        $data["HTTP_SID"] = $sid;

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: multipart/form-data"
            ),
        ));
        $response = curl_exec($curl);
        $return = trim($response, '﻿ ');
        $content = json_decode($response, true);
        $err = curl_error($curl);
        if (empty(!$err)) {
            throw new CmxException(\app\exceptions\code\Owncloud::OWNCLOUD_ERROR_500, json_encode($content));
        }
        curl_close($response);
        return $content;
    }

    public static function owncloudCM($url, $json, $settings = [])
    {
        $baseUrl = self::getUrl('cm');
        $url = $baseUrl . ltrim($url, '/');
        if (!isset($json['tokenLogin']) || $json['tokenLogin'] != true) {
            $sid = !isset($settings['sid']) && empty($settings['sid']) ? Session::getSessionId() : $settings['sid'];
            $url .= '?HTTP_SID=' . $sid;
        }

        $timeout = isset($settings['timeout']) && !empty($settings['timeout']) ? $settings['timeout'] : 10;
        $options = [
            'type' => self::POST_TYPE['json'],
            'data' => $json,
            'timeout' => $timeout,
        ];
        $return = self::post($url, $options);
        if (empty($return)) {
            return false;
        }

        $return = trim($return, '﻿ ');
        $content = json_decode($return, true);
        if (0 === $content['code']) {
            return $content['data'];
        }
        return false;
    }

    public static function getUrl($route='baseurl')
    {
        $config = Config::get('application');
        $domain = $config['application']['owncloud']['domain'];
        $url = $config['application']['owncloud'][$route];
        return 'http://' . $domain . $url . '/';
    }

    /* @deprecated 9.0后版本逐步弃用，使用getUrl方法 */
    public static function getORestfulBaseUrl()
    {
        $config = Config::get('application');
        $domain = $config['application']['owncloud']['domain'];
        $url = $config['application']['owncloud']['baseurl'];
        return 'http://' . $domain . $url . '/';
    }
}
