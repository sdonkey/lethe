<?php
namespace Lethe\Lib;

/**
 * Class Loader
 *
 * @author wuqiying@ruijie.com.cn
 */

class ClassLoader
{
    /**
     * @var array
     */
    private $maps = [];
    /**
     * @var bool
     */
    private $useNamespace = false;
    /**
     * @var bool
     */
    private $nameSuffix = true;
    /**
     * @var string
     */
    private $nameSeparator = '';
    /**
     * @param array $maps
     */
    public function setMap($maps)
    {
        $this->maps = $maps;
    }
    /**
     * @param bool $use
     */
    public function setUseNamespace($use)
    {
        $this->useNamespace = (bool) $use;
    }
    /**
     * @param bool $use
     */
    public function setNameSuffix($use)
    {
        $this->nameSuffix = (bool) $use;
    }
    /**
     * @param string $nameSeparator
     */
    public function setNameSeparator($nameSeparator)
    {
        $this->nameSeparator = (string) $nameSeparator;
    }
    /**
     * @param string $class
     * @return bool
     */
    public function load($class)
    {
        $suffix = null;
        $path = null;
        $match = false;
        foreach ($this->maps as $suffix => $path) {
            if (false !== strpos($class, $suffix)) {
                $match = true;
                break;
            }
        }
        if (false === $match) {
            return false;
        }

        if ($this->useNamespace) {
            $parts = explode('\\', $class);
        } else {
            if ($this->nameSeparator) {
                $class = str_replace($this->nameSeparator . $suffix, $suffix, $class);
            }
            $parts = explode('_', $class);
        }

        $file = array_pop($parts);

        $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (count($parts)) {
            $path .= strtolower(implode(DIRECTORY_SEPARATOR, $parts)) . DIRECTORY_SEPARATOR;
        }

        if ($this->nameSuffix) {
            $file = substr($file, 0, strlen($file) - strlen($suffix));
        }

        $file = $path . $file . '.php';

        if (is_readable($file)) {
            \Yaf\Loader::import($file);
            return true;
        }
        return false;
    }
}
