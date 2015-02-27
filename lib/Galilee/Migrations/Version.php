<?php

namespace Galilee\Migrations;

use Galilee\Migrations\Configuration\DefaultConfiguration;
use Galilee\Migrations\Exceptions\InvalidMigrationsClassException;
use Galilee\Migrations\Tools\OutputWriter;

class Version
{
    const STATE_NONE = 0;
    const STATE_PRE = 1;
    const STATE_EXEC = 2;
    const STATE_POST = 3;

    /** @var  DefaultConfiguration */
    private $configuration;

    /** @var  OutputWriter */
    private $outputWriter;

    /** @var  int */
    private $version;

    /** @var   */
    private $executeTime;

    /** @var  string */
    private $class;

    /** @var AbstractMigration */
    private $migration;

    /** @var int  */
    private $state = self::STATE_NONE;

    /**
     * @param  DefaultConfiguration            $configuration
     * @param $version
     * @param $class
     * @throws InvalidMigrationsClassException
     */
    public function __construct(DefaultConfiguration $configuration, $version, $class)
    {
        $this->setConfiguration($configuration);
        $this->setOutputWriter($configuration->getOutputWriter());
        $this->setClass($class);
        $this->setVersion($version);
        $this->setMigrationByClass($this->getClass());
    }

    /**
     * @return bool
     */
    public function isMigrated()
    {
        return $this->getConfiguration()->hasVersionMigrated($this);
    }

    /**
     *
     */
    public function markMigrated()
    {
        $this->getConfiguration()->addMigrationVersion($this);
    }

    /**
     *
     */
    public function markNotMigrated()
    {
        $this->getConfiguration()->addMigrationVersion($this);
    }

    /**
     * @param $direction
     * @param bool $dryRun
     */
    public function execute($direction, $dryRun = false)
    {
        // to be implemented
    }

    /*
     *  Getters / Setters
     */

    /**
     * @return DefaultConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param DefaultConfiguration $configuration
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return AbstractMigration
     */
    public function getMigration()
    {
        return $this->migration;
    }

    /**
     * @param AbstractMigration $migration
     */
    public function setMigration($migration)
    {
        $this->migration = $migration;
    }

    /**
     * @param string $className
     */
    public function setMigrationByClass($className)
    {
        $this->migration = (new \ReflectionClass($className))->newInstance($this);
    }

    /**
     * @return mixed
     */
    public function getExecuteTime()
    {
        return $this->executeTime;
    }

    /**
     * @param mixed $executeTime
     */
    public function setExecuteTime($executeTime)
    {
        $this->executeTime = $executeTime;
    }

    /**
     * @return OutputWriter
     */
    public function getOutputWriter()
    {
        return $this->outputWriter;
    }

    /**
     * @param OutputWriter $outputWriter
     */
    public function setOutputWriter($outputWriter)
    {
        $this->outputWriter = $outputWriter;
    }

    /**
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param int $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param int $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param $class
     * @throws InvalidMigrationsClassException
     */
    public function setClass($class)
    {
        if (!class_exists($class)) {
            throw new InvalidMigrationsClassException('Migration class `'.$class.'` does not exist.');
        }
        $this->class = $class;
    }
}
