<?php

namespace Ubikz\Migrations;

/**
 * Class ClassLoader.
 */
class ClassLoader
{
    /** @var string  */
    protected $fileExtension = '.php';

    /** @var null|string  */
    protected $namespace;

    /** @var null|string  */
    protected $includePath;

    /** @var string  */
    protected $namespaceSeparator = '\\';

    /**
     * @param null $namespace
     * @param null $includePath
     */
    public function __construct($namespace = null, $includePath = null)
    {
        $this->namespace = $namespace;
        $this->includePath = $includePath;
    }

    /**
     * @param $sep
     */
    public function setNamespaceSeparator($sep)
    {
        $this->namespaceSeparator = $sep;
    }

    /**
     * @return string
     */
    public function getNamespaceSeparator()
    {
        return $this->namespaceSeparator;
    }

    /**
     * @param $includePath
     */
    public function setIncludePath($includePath)
    {
        $this->includePath = $includePath;
    }

    /**
     * @return null|string
     */
    public function getIncludePath()
    {
        return $this->includePath;
    }

    /**
     * @param $fileExtension
     */
    public function setFileExtension($fileExtension)
    {
        $this->fileExtension = $fileExtension;
    }

    /**
     * @return string
     */
    public function getFileExtension()
    {
        return $this->fileExtension;
    }

    /**
     *
     */
    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }

    /**
     *
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }

    /**
     * @param $className
     *
     * @return bool
     */
    public function loadClass($className)
    {
        if (self::typeExists($className)) {
            return true;
        }
        if (!$this->canLoadClass($className)) {
            return false;
        }
        require($this->includePath !== null ? $this->includePath.DIRECTORY_SEPARATOR : '')
            .str_replace($this->namespaceSeparator, DIRECTORY_SEPARATOR, $className)
            .$this->fileExtension;

        return self::typeExists($className);
    }

    /**
     * @param $className
     *
     * @return bool
     */
    public function canLoadClass($className)
    {
        if ($this->namespace !== null && strpos($className, $this->namespace.$this->namespaceSeparator) !== 0) {
            return false;
        }
        $file = str_replace($this->namespaceSeparator, DIRECTORY_SEPARATOR, $className).$this->fileExtension;
        if ($this->includePath !== null) {
            return is_file($this->includePath.DIRECTORY_SEPARATOR.$file);
        }

        return (false !== stream_resolve_include_path($file));
    }

    /**
     * @param $className
     *
     * @return bool
     */
    public static function classExists($className)
    {
        return self::typeExists($className, true);
    }

    /**
     * @param $className
     *
     * @return mixed|null
     */
    public static function getClassLoader($className)
    {
        foreach (spl_autoload_functions() as $loader) {
            if (is_array($loader)
                && ($classLoader = reset($loader))
                && $classLoader instanceof ClassLoader
                && $classLoader->canLoadClass($className)
            ) {
                return $classLoader;
            }
        }

        return;
    }

    /**
     * @param $type
     * @param bool $autoload
     *
     * @return bool
     */
    private static function typeExists($type, $autoload = false)
    {
        return class_exists($type, $autoload)
        || interface_exists($type, $autoload)
        || (function_exists('trait_exists') && trait_exists($type, $autoload));
    }
}
