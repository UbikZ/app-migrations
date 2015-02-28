<?php

namespace Galilee\Migrations;

use Galilee\Migrations\Configuration\DefaultConfiguration;
use Galilee\Migrations\Exceptions\InvalidMigrationsClassException;
use Symfony\Component\Console\Output\OutputInterface;

class Version
{
    const STATE_NONE = 0;
    const STATE_PRE = 1;
    const STATE_EXEC = 2;
    const STATE_POST = 3;

    /** @var  DefaultConfiguration */
    private $configuration;

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
     * @param DefaultConfiguration $configuration
     * @param $version
     * @param $class
     *
     * @throws InvalidMigrationsClassException
     */
    public function __construct(DefaultConfiguration $configuration, $version, $class)
    {
        $this->setConfiguration($configuration);
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
        $this->getConfiguration()->removeMigrationVersion($this);
    }

    /**
     * @return string
     */
    public function getExecutionState()
    {
        switch ($this->state) {
            case self::STATE_PRE:
                return 'Pre-Checks';
            case self::STATE_POST:
                return 'Post-Checks';
            case self::STATE_EXEC:
                return 'Execution';
            default:
                return 'No State';
        }
    }

    /**
     * @param $direction
     * @param OutputInterface $output
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function execute($direction, OutputInterface $output)
    {
        try {
            $start = microtime(true);
            $this->setState(self::STATE_PRE);
            $this->getMigration()->{'pre'.ucfirst($direction)}();
            if ($direction === 'up') {
                $output->writeln("\n".sprintf('  <info>++</info> migrating <comment>%s</comment>', $this->version)."\n");
            } else {
                $output->writeln("\n".sprintf('  <info>--</info> reverting <comment>%s</comment>', $this->version)."\n");
            }
            $this->setState(self::STATE_EXEC);
            $this->getMigration()->$direction();
            $output->writeln('    <comment>-></comment> Executing...');
            if ($direction === 'up') {
                $this->markMigrated();
            } else {
                $this->markNotMigrated();
            }

            $this->setState(self::STATE_POST);
            $this->getMigration()->{'post'.ucfirst($direction)}();
            $end = microtime(true);
            $this->setExecuteTime(round($end - $start, 2));
            if ($direction === 'up') {
                $output->writeln(sprintf("\n  <info>++</info> migrated (%ss)", $this->getExecuteTime()));
            } else {
                $output->writeln(sprintf("\n  <info>--</info> reverted (%ss)", $this->getExecuteTime()));
            }
            $this->setState(self::STATE_NONE);

            return true;
        } catch (\Exception $e) {
            $output->writeln(sprintf(
                '<error>Migration %s failed during %s. Error %s</error>',
                $this->version, $this->getExecutionState(), $e->getMessage()
            ));
            $this->state = self::STATE_NONE;
            throw $e;
        }
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
     *
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
