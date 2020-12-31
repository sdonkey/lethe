<?php
namespace Lethe\Lib;

/**
 * 常用工具类
 * @author: wjr<wangjr129@163.com>
 * @Date: 2017/12/18 22:45
 */
use app\services\user\User as UserService;
use app\services\system\Util as SysServiceUtil;
use Lethe\Lib\Log;

class Util
{
    /**
     * Generates a random string of a given type and length.
     *
     *
     * $str = Text::random(); // 8 character random string
     *
     * The following types are supported:
     *
     * alnum
     * :  Upper and lower case a-z, 0-9 (default)
     *
     * alpha
     * :  Upper and lower case a-z
     *
     * hexdec
     * :  Hexadecimal characters a-f, 0-9
     *
     * distinct
     * :  Uppercase characters and numbers that cannot be confused
     *
     * You can also create a custom type by providing the "pool" of characters
     * as the type.
     *
     * @param   string   a type of pool, or a string of characters to use as the pool
     * @param   integer  length of string to return
     * @param null|mixed $type
     * @param mixed $length
     * @return  string
     * @uses    UTF8::split
     */
    public static function random($type = null, $length = 8)
    {
        if ($type === null) {
            // Default is to generate an alphanumeric string
            $type = 'alnum';
        }

        #$utf8 = FALSE;

        switch ($type) {
            case 'alnum':
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'alpha':
                $pool = 'abcdefghijklmnopqrstuvwxyz0123456789';
                break;
            case 'group':
                $pool = 'abcdefghjkmnpqrstuvwxyz23456789';
                break;
            case 'hexdec':
                $pool = '0123456789abcdef';
                break;
            case 'numeric':
                $pool = '0123456789';
                break;
            case 'nozero':
                $pool = '123456789';
                break;
            case 'distinct':
            default:
                $pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
                break;
//            default :
//                $pool = (string) $type;
//                $utf8 = !self::isAscii($pool);
//                break;
        }

        // Split the pool into an array of characters
        $pool = str_split($pool, 1);

        // Largest pool key
        $max = count($pool) - 1;

        $str = '';
        for ($i = 0; $i < $length; $i++) {
            // Select a random character from the pool and add it to the string
            $str .= $pool[mt_rand(0, $max)];
        }

        // Make sure alnum strings contain at least one letter and one digit
        if ($type === 'alnum' and $length > 1) {
            if (ctype_alpha($str)) {
                // Add a random digit
                $str[mt_rand(0, $length - 1)] = chr(mt_rand(48, 57));
            } elseif (ctype_digit($str)) {
                // Add a random letter
                $str[mt_rand(0, $length - 1)] = chr(mt_rand(65, 90));
            }
        }

        return $str;
    }

    /**
     * Tests whether a string contains only 7-bit ASCII bytes. This is used to
     * determine when to use native functions or UTF-8 functions.
     *
     * $ascii = UTF8::isAscii($str);
     *
     * @param   mixed    string or array of strings to check
     * @param mixed $str
     * @return  boolean
     */
    public static function isAscii($str)
    {
        if (is_array($str)) {
            $str = implode('', $str);
        }
        return !preg_match('/[^\x00-\x7F]/S', $str);
    }

    /**
     * 生成uuid
     * @return string
     */
    public static function makeUuid()
    {
        return md5(uniqid(mt_rand(), true));
    }

    public static function makeUuidWithLock()
    {
        $lock = new Lock();
        $res = $lock->lock("import_user", true);
        if ($res) {
            $uuid = md5(uniqid(mt_rand(), true));
        }
        $lock->unlock("import_user");
        return $uuid;
    }

    /**
     * 参数剔除助手函数
     * @param array $data 参数数组
     * @param array|string $fields 要剔除的字段 ['dd',da] or dd,da
     * @return array data
     */
    public static function rmSubscript(&$data, $fields)
    {
        is_string($fields) && $fields = explode(',', $fields);
        if (is_array($fields)) {
            array_walk($fields, function ($v) use (&$data) {
                if (array_key_exists($v, $data)) {
                    unset($data[$v]);
                }
            });
        }
        array_walk($data, function ($v, $k) use (&$data) {
            if (str_contains($k, '/')) {
                unset($data[$k]);
            }
        });
    }

    /**
     * 单个关联数组根据某个字段排序
     * @param $data
     * @param $order
     * @param int $type
     * @param int $sortFlag
     * @return mixed
     */
    public static function sortByOneField($data, $order, $type = SORT_ASC, $sortFlag = SORT_REGULAR)
    {
        if (count($data) <= 0) {
            return $data;
        }
        $temp = [];
        foreach ($data as $key => $value) {
            $temp[$key] = $value[$order];
        }
        array_multisort($temp, $type, $sortFlag, $data);
        return $data;
    }

    /** 单个关联数据根据某两个字段排序
     *
     * @param array $data
     * @param string $order1
     * @param string $order2
     * @param int $type1
     * @param int $type2
     * @param int $sortFlag1
     * @param int $sortFlag2
     * @return mixed
     */
    public static function sortByTwoFields($data, $order1, $order2, $type1 = SORT_ASC, $type2 = SORT_ASC, $sortFlag1 = SORT_REGULAR, $sortFlag2 = SORT_REGULAR)
    {
        if (count($data) <= 0) {
            return $data;
        }
        $temp1 = [];
        $tmp2 = [];
        foreach ($data as $key => $value) {
            $tmp1[$key] = $value[$order1];
            $temp2[$key] = $value[$order2];
        }
        array_multisort($temp1, $type1, $sortFlag1, $tmp2, $type2, $sortFlag2, $data);
        return $data;
    }

    /**
     * @brief Generates a cryptographic secure pseudo-random string
     * @param Int $length of the random string
     * @return String
     * Please also update secureRNGAvailable if you change something here
     */
    public static function generateRandomBytes($length = 30)
    {
        // Try to use openssl_random_pseudo_bytes
        if (function_exists('openssl_random_pseudo_bytes')) {
            $pseudoByte = bin2hex(openssl_random_pseudo_bytes($length, $strong));
            if ($strong == true) {
                return substr($pseudoByte, 0, $length); // Truncate it to match the length
            }
        }

        // Try to use /dev/urandom
        if (!self::runningOnWindows()) {
            $fp = @file_get_contents('/dev/urandom', false, null, 0, $length);
            if ($fp !== false) {
                $string = substr(bin2hex($fp), 0, $length);
                return $string;
            }
        }

        // Fallback to mt_rand()
        $characters = '0123456789';
        $characters .= 'abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters) - 1;
        $pseudoByte = "";

        // Select some random characters
        for ($i = 0; $i < $length; $i++) {
            $pseudoByte .= $characters[mt_rand(0, $charactersLength)];
        }
        return $pseudoByte;
    }

    /**
     * @return bool - well are we running on windows or not
     */
    public static function runningOnWindows()
    {
        return (substr(PHP_OS, 0, 3) === "WIN");
    }

    /**
     * 获取硬盘剩余存储大小
     *
     * @return int 返回剩余存储大小
     */
    public static function getSizeFree()
    {
        $storage_path = self::getOwncloudStorageDir();
        $free_space = disk_free_space($storage_path);
        return $free_space;
    }

    /**
     * 获取硬盘总大小
     *
     * @return int 返回硬盘总大小
     */
    public static function getSizeTotal()
    {
        $storage_path = self::getOwncloudStorageDir();
        $total_space = disk_total_space($storage_path);
        return $total_space;
    }

    /**
     * 获取data目录已用存储
     *
     * @return int 返回已用存储大小
     */
    public static function getSizeUsed()
    {
        $dir = self::getOwncloudStorageDir();//修改为外置存储的挂载点   realpath(dirname(ROOT_PATH)) . '/owncloud/data';
        $size = self::dirSize($dir);
        return $size;
    }

    /**
     * php遍历文件夹下的所有文件及其子文件夹下所有的文件并计算出其所占的磁盘空间
     *
     * @param string $dir
     * @param string $exclude 排除的文件、文件夹
     * @return int 返回目录大小
     */
    public static function dirSize($dir, $exclude = "")
    {
        $size = 0;      //初始大小为0
        try {
            $io = popen('/usr/bin/du -s "' .$dir. '" --exclude="'.$exclude.'"', 'r');
            if ($io) {
                $size = fgets($io, 4096);
                $size = substr($size, 0, strpos($size, "\t"));
                pclose($io);
            }
        } catch (\Exception $e) {
            $size = 0;
            Log::error(__METHOD__ . $e->getMessage(), [], 'dirSize');
        }
        $size = (int) $size * 1024;
        return $size;
    }

    /**
     * 获取机器网卡的物理（MAC）地址(获取网卡的MAC地址原码)
     *
     * @return string
     */
    public static function getMAC()
    {
        $eth0 = exec("cat /sys/class/net/eth0/address");
        if ($eth0 == "") {
            $eth1 = exec("cat /sys/class/net/eth1/address");
            $mac = $eth1;
        } else {
            $mac = $eth0;
        }
        if (empty($mac)) {
            $mac = "invalidation";
        }
        return $mac;
    }

    public static function postCurl($url, $data_string, &$http_code)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);//超时时间120秒
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)
            ]
        );
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            //捕抓异常
            Log::error($data_string, [], 'curl');
            throw new \app\exceptions\CmxException(\app\exceptions\code\App::CURL_REQUEST_FAIL);
        }
        curl_close($ch);//关闭
        return $response;
    }

    /**
     * 校验api权限(登录、requesttoken)
     *
     * @param string $action 方法名称
     * @param array $excluded 排除的方法数组(不登录也可访问)
     * @throws \app\exceptions\CmxException
     */
    public static function authorizeApiRequest($action, $excluded = [])
    {
        if (!in_array($action, $excluded)) {
            self::checkLoggedIn();
        }
    }

    public static function checkApiLoginUserIsAdmin($action, $excluded = [])
    {
        if (!in_array($action, $excluded)) {
            $uid = UserService::getCurrentUser();
            if ($uid != "admin") {
                throw new \app\exceptions\CmxException(\app\exceptions\code\App::NOT_PERMISSION);
            }
        }
    }

    /**
     * 登录校验
     *
     * @throws \app\exceptions\CmxException
     * @return bool
     */
    public static function checkLoggedIn()
    {
        if (!UserService::isLogin()) {
            throw new \app\exceptions\CmxException(\app\exceptions\code\App::NOT_PERMISSION, 'Logged in first!');
        }

        return true;
    }

    /**
     * CSRF的token校验
     *
     * @param string $token
     * @throws \app\exceptions\CmxException
     * @return bool
     */
    public static function checkCSRF($token = '')
    {
        if (empty($token)) {
            if (!empty($_SERVER['HTTP_REQUESTTOKEN'])) {
                $token = $_SERVER['HTTP_REQUESTTOKEN'];
            } elseif (!empty($_REQUEST['requesttoken'])) {
                $token = $_REQUEST['requesttoken'];
            }
        }

        if (empty(Session::get('requesttoken'))) {
            throw new \app\exceptions\CmxException(\app\exceptions\code\App::NOT_PERMISSION, 'CSRF check failed');
        }

        if ($token != Session::get('requesttoken')) {
            throw new \app\exceptions\CmxException(\app\exceptions\code\App::NOT_PERMISSION, 'CSRF check failed');
        }

        return true;
    }

    public static function getTotalPage($page_size, $total)
    {
        if (($total % $page_size) == 0) {
            return (int) ($total/$page_size);
        }
        return (int) ($total/$page_size) + 1;
    }

    public static function getClassIdByUid($uid)
    {
        if (empty($uid)) {
            return "";
        }
        $class_id_list = \app\models\GroupUser::single()->queryGroupByUids([$uid]);
        return !empty($class_id_list) ? $class_id_list[0]['gid'] : null;
    }

    public static function getUidsByClassId($class_id)
    {
        if (empty($class_id)) {
            return [];
        }
        $user_list = (new \app\models\GroupUser())->queryUserByGid($class_id);
        return array_column($user_list, 'uid');
    }

    public static function getNameByUid($uid)
    {
        if (empty($uid)) {
            return "";
        }
        $user_info = \app\models\User::single()->getUsersByUids($uid, ['displayname'])[0];
        return $user_info['displayname'];
    }

    public static function getNameListByUids($uids)
    {
        $display_name_list = [];
        if (empty($uids)) {
            return $display_name_list;
        }
        $user_info = \app\models\User::single()->getUsersByUids($uids);
        foreach ($user_info as $user) {
            $display_name_list[$user['uid']] = $user['displayname'];
        }
        return $display_name_list;
    }

    public static function getUidsStringArr($uids)
    {
        $uids = is_array($uids) ? $uids : [$uids];
        return array_map('strval', $uids);
    }

    public static function getPresentList()
    {
        /* $present_config_str = '["101|Cake|Sweetheart Cake|101.png|101.png|101.png|5|Wish you happiness!|","102|Cake|Cat Paw Cake|102.png|102.png|102.png|5|Happy birthday!|","103|Cake|DORAEMON Cake|103.png|103.png|103.png|5|May all your wishes come true!|","104|Cake|Chocolate Cake|104.png|104.png|104.png|5|Wish you a sweet life!|","105|Cake|Rainbow Cake|105.png|105.png|105.png|5|Wish you a colorful life!|","106|Cake|Dove Cake|106.gif|106.gif|106.gif|10|Wish you a good time!|","201|Flower|Sunflower|201.png|201.png|201.png|5|May you blossom in sunshine!|","202|Flower|Lucky Clover|202.png|202.png|202.png|5|May you enjoy the sunshine!|","203|Flower|Garland|203.png|203.png|203.png|5|Wish you forever youth!|","204|Flower|Send Flowers|204.gif|204.gif|204.gif|10|May you blossom like flowers!|","205|Flower|Lollipop Flower|205.png|205.png|205.png|5|Wish you a good mood!|","206|Flower|Fingers Flower|206.png|206.png|206.png|5|Wish you a great day!|"]';
         $present_config_str = json_decode($present_config_str,true);
         AppconfigModel::single()->create(
             [
                 'module'=>'present',
                 'configkey'=>'present_config',
                 'configvalue'=> json_encode($present_config_str)
             ]
         );*/

        $present_config = AppconfigModel::single()->getValue('present', 'present_config');
        $present_config_list = json_decode($present_config['configvalue'], true);
        $present_list = [];
        foreach ($present_config_list as $present_config) {
            $present_info = explode("|", $present_config);
            if (count($present_info) == 9) {
                $id = $present_info[0];
                $img_name = $present_info[3];
                $present_list[$id] = [
                    'id' => $id,
                    'type' => $present_info[1],
                    'name' => $present_info[2],
                    'pic_small' => '/images/present/small/'.$img_name,
                    'pic_big' => '/images/present/big/'.$img_name,
                    'pic_normal' => '/images/present/normal/'.$img_name,
                    'credits' => $present_info[6],
                    'wish' => $present_info[7],
                ];
            }
        }
        return $present_list;
    }

    /**
     * 截取字符串长度
     *
     * @param string $string
     * @param int $length
     * @param string $dot
     * @param string $charset
     *
     * @return mixed
     */
    public static function cutstr($string, $length, $dot = ' ...', $charset = 'utf-8')
    {
        if (strlen($string) <= $length) {
            return $string;
        }

        $pre = chr(1);
        $end = chr(1);
        $string = str_replace(['&amp;', '&quot;', '&lt;', '&gt;'], [$pre . '&' . $end, $pre . '"' . $end, $pre . '<' . $end, $pre . '>' . $end], $string);

        $strcut = '';
        if (strtolower($charset) == 'utf-8') {
            $n = $tn = $noc = 0;
            while ($n < strlen($string)) {
                $t = ord($string[$n]);
                if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                    $tn = 1;
                    $n++;
                    $noc++;
                } elseif (194 <= $t && $t <= 223) {
                    $tn = 2;
                    $n += 2;
                    $noc += 2;
                } elseif (224 <= $t && $t <= 239) {
                    $tn = 3;
                    $n += 3;
                    $noc += 2;
                } elseif (240 <= $t && $t <= 247) {
                    $tn = 4;
                    $n += 4;
                    $noc += 2;
                } elseif (248 <= $t && $t <= 251) {
                    $tn = 5;
                    $n += 5;
                    $noc += 2;
                } elseif ($t == 252 || $t == 253) {
                    $tn = 6;
                    $n += 6;
                    $noc += 2;
                } else {
                    $n++;
                }

                if ($noc >= $length) {
                    break;
                }
            }
            if ($noc > $length) {
                $n -= $tn;
            }

            $strcut = substr($string, 0, $n);
        } else {
            $_length = $length - 1;
            for ($i = 0; $i < $length; $i++) {
                if (ord($string[$i]) <= 127) {
                    $strcut .= $string[$i];
                } elseif ($i < $_length) {
                    $strcut .= $string[$i] . $string[++$i];
                }
            }
        }

        $strcut = str_replace([$pre . '&' . $end, $pre . '"' . $end, $pre . '<' . $end, $pre . '>' . $end], ['&amp;', '&quot;', '&lt;', '&gt;'], $strcut);

        $pos = strrpos($strcut, chr(1));
        if ($pos !== false) {
            $strcut = substr($strcut, 0, $pos);
        }
        return $strcut . $dot;
    }

    /**
     * 生成uuid
     * @param mixed $str
     * @return string
     */
    public static function makeUniqUuid($str = "")
    {
        return md5(uniqid(mt_rand(), true).microtime().$str);
    }

    public static function getRcdcDependStatus()
    {
        $rcdc_config = Config::get('rcdc');
        return  $rcdc_config['depend'];
    }

    public static function getUserDirUid($uid)
    {
        // xxx/
        return realpath(self::getOwncloudStorageDir() . "/" . $uid .'/files');
    }

    public static function getOwncloudDir()
    {
        // xxx/
        return realpath(ROOT_PATH . '/../../public/owncloud');
    }

    public static function getOwncloudStorageDir()
    {
        // xxx/
        return self::getOwncloudDir()."/data";
    }

    public static function export($data, $filename)
    {
        $data = iconv('utf-8', 'gb2312', $data);
        header('Content-Type: application/octetstream; charset=gb2312');
        $now = gmdate('D, d M Y H:i:s') . ' GMT';
        header('Expires: ' . $now);
        $filename = iconv('utf-8', 'gb2312', $filename);
        header('Content-Disposition: attachment; filename="'.$filename.'.csv"');
        header('Pragma: no-cache');
        header('Pragma: public');
        echo $data;
    }
}
