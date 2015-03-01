<?php

namespace Ubikz\Migrations\Configuration\Adapters;

use Ubikz\Migrations\Configuration\DefaultConfiguration;
use Ubikz\Migrations\Exceptions\InvalidConfigurationFileException;

abstract class AbstractFileConfiguration implements InterfaceFileConfiguration
{
    /** @var  string */
    private $filepath;

    /** @var bool  */
    private $loaded = false;

    final public function load(DefaultConfiguration &$conf, $filepath)
    {
        if ($this->getLoaded()) {
            throw new InvalidConfigurationFileException('File already loaded.');
        }
        if (!file_exists($filepath)) {
            throw new InvalidConfigurationFileException('File cannot be found.');
        }
        $this->setFilepath($filepath);
        $this->extract($conf, $filepath);
        $this->setLoaded(true);
    }

    /**
     * @param DefaultConfiguration $conf
     * @param $file
     *
     * @return mixed
     */
    abstract public function extract(DefaultConfiguration &$conf, $file);

    /*
     * Getters / Setters
     */

    /**
     * @return string
     */
    public function getFilepath()
    {
        return $this->filepath;
    }

    /**
     * @param string $filepath
     */
    public function setFilepath($filepath)
    {
        $this->filepath = $filepath;
    }

    /**
     * @return string
     */
    public function getLoaded()
    {
        return $this->loaded;
    }

    /**
     * @param string $loaded
     */
    public function setLoaded($loaded)
    {
        $this->loaded = $loaded;
    }
}
