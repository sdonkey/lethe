<?php
namespace Lethe\Lib;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem as Base;

class Filesystem
{
    private static $adapter = null;

    private static $filesystem = null;

    private static function init()
    {
        self::$adapter = new Local(
            realpath('/'),
            LOCK_EX,
            Local::DISALLOW_LINKS,
            [
                'file' => [
                    'public' => 0775,
                    'private' => 0700,
                ],
                'dir' => [
                    'public' => 0775,
                    'private' => 0700,
                ]
            ]
        );
        self::$filesystem = new Base(self::$adapter, ['visibility' => 'public']);
    }

    /**
     * @return Base
     */
    public static function getFilesystem()
    {
        if (empty(self::$filesystem)) {
            self::init();
        }

        return self::$filesystem;
    }

    public static function has($path)
    {
        return self::getFilesystem()->has($path);
    }

    public static function get($path)
    {
        return self::getFilesystem()->get($path);
    }

    public static function createDir($dirname, array $config = [])
    {
        return self::getFilesystem()->createDir($dirname, $config);
    }

    public static function deleteDir($dirname)
    {
        return self::getFilesystem()->deleteDir($dirname);
    }

    public static function moveDir($sourcePath, $targetPath)
    {
        return self::getFilesystem()->rename($sourcePath, $targetPath);
    }

    public static function moveFile($sourcePath, $targetPath, $filename)
    {
        $path = $sourcePath . '/' . $filename;
        $newPath = $targetPath . '/' . $filename;
        return self::getFilesystem()->rename($path, $newPath);
    }

    public static function copyFile($path, $newPath)
    {
        return self::getFilesystem()->copy($path, $newPath);
    }

    public static function deleteFile($path)
    {
        return self::getFilesystem()->delete($path);
    }

    public static function rename($path, $oldName, $newName)
    {
        $oldPath = rtrim($path, '/') . '/' . ltrim($oldName, '/');
        $newPath = rtrim($path, '/') . '/' . ltrim($newName, '/');
        return self::getFilesystem()->rename($oldPath, $newPath);
    }

    public static function createFile($path, $contents, array $config = [])
    {
        return self::getFilesystem()->put($path, $contents, $config);
    }

    public static function listFiles($path)
    {
        return self::getFilesystem()->listContents($path);
    }
}
