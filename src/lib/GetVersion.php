<?php
namespace Lethe\Lib;

/**
 * 获取比较系统版本号
 * User: yuanjiwei@ruijie.com
 * Date: 2018/12/11
 * Time: 15:03
 */

class GetVersion
{
    public static function compareVersion()
    {
        $teaVersion = isset($_GET["teaversion"]) ? stripslashes($_GET["teaversion"]) : '';
        $stuVersion = isset($_GET["stuversion"]) ? stripslashes($_GET["stuversion"]) : '';
        $currentVersion = self::getCurrentVersion();
        if (!empty($teaVersion)) {
            $result = self::compareTeaVersion($teaVersion, $currentVersion);
        } elseif (!empty($stuVersion)) {
            $result = self::compareStuVersion($stuVersion, $currentVersion);
        } else {
            $result = false;
        }

        return [
            'code' => 0,
            'data' => [
                "version" => $currentVersion,
                "compare"  => $result
            ]
        ];
    }

    protected static function getCurrentVersion()
    {
        $str = file_get_contents(ROOT_PATH . "/RJversion");//获得内容
        $arr = explode("\n", $str);//分行存入数组
        $string1 = "ruijie.rcc.workspaceSERVER.mainVersion=";
        $string2 = "ruijie.rcc.workspaceSERVER.minorVersion=";
        $string3 = "ruijie.rcc.workspaceSERVER.threeVersion=";
        $string4 = "ruijie.rcc.workspaceSERVER.fourVersion=";
        $versions = [];
        foreach ($arr as $value) {
            //版本号1
            if (strstr($value, $string1)) {
                $versions[0] = ltrim($value, $string1);
            }
            //版本号2
            if (strstr($value, $string2)) {
                $versions[1] = ltrim($value, $string2);
            }
            //版本号3
            if (strstr($value, $string3)) {
                $versions[2] = ltrim($value, $string3);
            }
            //版本号4
            if (strstr($value, $string4)) {
                $versions[3] = ltrim($value, $string4);
            }
        }
        $currentVersion = $versions[0].".".$versions[1].".".$versions[2].".".$versions[3];
        return $currentVersion;
    }

    protected static function isVersionCompatible($versions, $serverRequired, $clientRequired)
    {
        $clientVersions = explode('.', $clientRequired);
        $clientVersionNum =  (int)$clientVersions[0]*10000+(int)$clientVersions[1]*1000+(int)$clientVersions[2]*100+(int)$clientVersions[3];
        $serverVersions = explode('.', $serverRequired);
        $serverVersionNum =  (int)$serverVersions[0]*10000+(int)$serverVersions[1]*1000+(int)$serverVersions[2]*100+(int)$serverVersions[3];

        //查找当前服务器版本对应的客户端版本号
        $result = false;
        $requiredVersion = 0;
        foreach ($versions as $server) {
            $serverLimits = explode('.', $server);
            $serverlimitNum =  (int)$serverLimits[0]*10000+(int)$serverLimits[1]*1000+(int)$serverLimits[2]*100+(int)$serverLimits[3];
            //限制版本号<服务器版本号
            if ($serverlimitNum < $serverVersionNum) {
                $result = true;
                $requiredVersion = $serverlimitNum;
            } elseif ($serverlimitNum == $serverVersionNum) {
                $requiredVersion = $serverlimitNum;
                break;
            } else {
                if ($result == true) {
                    break;
                }
            }
        }

        if ($requiredVersion == 0) {
            return false;
        } elseif ($requiredVersion < $clientVersionNum) {
            return true;
        } elseif ($requiredVersion == $clientVersionNum) {
            return true;
        }
        return false;
    }

    protected static function compareTeaVersion($teaVersion, $currentVersion)
    {
        $versions = [];
        $str = file_get_contents(ROOT_PATH . "/TeaVersion.txt");//获得内容
        $versions = explode(";\r\n", $str);//分行存入数组
        $result = self::isVersionCompatible($versions, $currentVersion, $teaVersion);
        return $result;
    }

    protected static function compareStuVersion($stuVersion, $currentVersion)
    {
        $versions = [];
        $str = file_get_contents(ROOT_PATH . "/StuVersion.txt");//获得内容
        $versions = explode(";\r\n", $str);//分行存入数组
        $result = self::isVersionCompatible($versions, $currentVersion, $stuVersion);
        return $result;
    }
}
