<?php

namespace Galilee\Migrations\Configuration;

use Galilee\Migrations\AbstractMigration;
use Galilee\Migrations\Exceptions\InvalidMigrationsClassException;
use Galilee\Migrations\Exceptions\InvalidMigrationsDirectoryException;
use Galilee\Migrations\Exceptions\InvalidMigrationsFileException;
use Galilee\Migrations\Exceptions\InvalidMigrationsNamespaceException;
use Galilee\Migrations\Exceptions\InvalidMigrationsVersionException;
use Galilee\Migrations\Tools\OutputWriter;
use Galilee\Migrations\Version;

/**
 * Class Configuration
 * @package Galilee\Migrations\Configuration
 */
class DefaultConfiguration
{
    /** @var  string */
    private $name;

    /** @var  OutputWriter */
    private $outputWriter;

    /** @var bool  */
    private $migrationsFileCreated = false;

    /** @var string  */
    private $migrationsFileName = 'migration_versions';

    /** @var string */
    private $migrationsDirectory;

    /** @var string */
    private $migrationsNamespace;

    /** @var array */
    private $migrationsList;

    /**
     * @param OutputWriter $outputWriter
     */
    public function __construct(OutputWriter $outputWriter = null)
    {
        $this->setOutputWriter((null === $outputWriter) ? new OutputWriter() : $outputWriter);
    }

    /**
     * @param $filepath
     * @param $type
     * @throws \Galilee\Migrations\Exceptions\InvalidAdapterFileException
     */
    public function importConfiguration($filepath, $type)
    {
        $extractManager = FactoryFileConfiguration::create($type);
        $extractManager->extract($this, $filepath);
    }

    /**
     * @throws InvalidMigrationsDirectoryException
     * @throws InvalidMigrationsNamespaceException
     */
    public function validate()
    {
        if (null === $this->getMigrationsNamespace()) {
            throw new InvalidMigrationsNamespaceException('Migrations namespace is required.');
        }
        if (null === $this->getMigrationsDirectory()) {
            throw new InvalidMigrationsDirectoryException('Migrations directory is required.');
        }
    }

    /**
     * @param $path
     * @return array
     * @throws InvalidMigrationsClassException
     */
    public function registerMigrationsFromDirectory($path)
    {
        $files = glob(rtrim(realpath($path, '/')).'/Version*.php');
        $versions = array();
        if ($files && is_array($files)) {
            foreach ($files as $file) {
                require_once $file;
                $info = pathinfo($file);
                $version = substr($info['filename'], 7);
                $class = $this->migrationsNamespace.'\\'.$info['filename'];
                if (!class_exists($class)) {
                    throw new InvalidMigrationsClassException('Class `'.$class.'` cannot be found.');
                }
                $versions[] = $this->registerMigration($version, $class);
            }
        }

        return $versions;
    }

    /**
     * @param $version
     * @param $class
     * @return Version|string
     * @throws InvalidMigrationsVersionException
     */
    public function registerMigration($version, $class)
    {
        $version = (string) $version;
        $class = (string) $class;
        if (isset($this->getMigrationsList()[$version])) {
            throw new InvalidMigrationsVersionException('This migration have been duplicated.');
        }
        $version = new Version($this, $version, $class);
        $this->migrationsList[$version->getVersion()] = $version;
        ksort($this->migrationsList);

        return $version;
    }

    /**
     * @param $version
     * @return bool
     */
    public function hasVersion($version)
    {
        return isset($this->getMigrationsList()[$version]);
    }

    /**
     * @param  Version                        $version
     * @return bool
     * @throws InvalidMigrationsFileException
     */
    public function hasVersionMigrated(Version $version)
    {
        $this->createMigrationFile();

        return in_array($version->getVersion(), $this->readMigrationFile());
    }

    /**
     * @param  Version                        $version
     * @throws InvalidMigrationsFileException
     */
    public function addMigrationVersion(Version $version)
    {
        $this->createMigrationFile();
        $versions = $this->readMigrationFile();
        $versions[$version->getVersion()] = $version->getVersion();
        $this->writeMigrationFile($versions);
    }

    /**
     * @param  Version                           $version
     * @throws InvalidMigrationsFileException
     * @throws InvalidMigrationsVersionException
     */
    public function removeMigrationVersion(Version $version)
    {
        $this->createMigrationFile();
        $versions = $this->readMigrationFile();
        if (!isset($versions[$version->getVersion()])) {
            throw new InvalidMigrationsVersionException('Cannot remove `'.$version->getVersion().'`.');
        }
        unset($versions[$version->getVersion()]);
        $this->writeMigrationFile($versions);
    }

    /**
     * @return mixed
     * @throws InvalidMigrationsFileException
     */
    public function getMigratedVersions()
    {
        $this->createMigrationFile();

        return $this->readMigrationFile();
    }

    /**
     * @return array
     */
    public function getAvaiblableVersions()
    {
        $availableVersion = array();
        foreach ($this->getMigrationsList() as $migration) {
            $availableVersion[] = $migration->getVersion();
        }

        return $availableVersion;
    }

    /**
     * @return string
     * @throws InvalidMigrationsFileException
     */
    public function getCurrentVersion()
    {
        $this->createMigrationFile();
        $versions = $this->readMigrationFile();

        $migratedVersions = array();
        if ($this->getMigrationsList() && is_array($this->getMigrationsList())) {
            foreach ($this->getMigrationsList() as $migration) {
                $migratedVersions[] = $migration->getVersion();
            }
        }
        $intersect = array_intersect($versions, $migratedVersions);
        $intersect = count($intersect) > 0 ? $intersect : $versions;

        return is_array($intersect) ? sprintf('%s', max($intersect)) : '0';
    }

    /**
     * @return null|string
     */
    public function getPrevVersion()
    {
        return $this->getRelativeVersion($this->getCurrentVersion(), -1);
    }

    /**
     * @return null|string
     */
    public function getNextVersion()
    {
        return $this->getRelativeVersion($this->getCurrentVersion(), 1);
    }

    /**
     * @return string
     */
    public function getLatestVersion()
    {
        $versions = array_keys($this->getMigrationsList());
        $latest = end($versions);

        return $latest !== false ? (string) $latest : '0';
    }

    /**
     * @param $version
     * @param $delta
     * @return null|string
     */
    private function getRelativeVersion($version, $delta)
    {
        $versions = array_keys($this->getMigrationsList());
        array_unshift($version, 0);
        $offset = array_search($version, $versions);
        if ($offset === false || !isset($versions[$offset + $delta])) {
            return null;
        }

        return (string) $versions[$offset + $delta];
    }

    /**
     * @param $alias
     * @return null|string
     */
    public function resolveVersionAlias($alias)
    {
        if ($this->hasVersion($alias)) {
            return $alias;
        }
        switch ($alias) {
            case 'first':
                return '0';
            case 'current':
                return $this->getCurrentVersion();
            case 'prev':
                return $this->getPrevVersion();
            case 'next':
                return $this->getNextVersion();
            case 'latest':
                return $this->getLatestVersion();
            default:
                return null;
        }
    }

    /**
     * @return bool
     * @throws InvalidMigrationsDirectoryException
     * @throws InvalidMigrationsFileException
     * @throws InvalidMigrationsNamespaceException
     */
    public function createMigrationFile()
    {
        $created = false;
        $this->validate();

        if (!$this->isMigrationsFileCreated()) {
            if (false === @touch($this->getMigrationsDirectory().'/'.$this->getMigrationsFileName())) {
                throw new InvalidMigrationsFileException('Cannot create migrations file.');
            }
            $created = true;
            $this->setMigrationsFileCreated($created);
        }

        return $created;
    }

    /**
     * @return mixed
     * @throws InvalidMigrationsFileException
     */
    private function readMigrationFile()
    {
        $filePath = $this->getMigrationsDirectory().'/'.$this->getMigrationsFileName();
        if (!file_exists($filePath)) {
            throw new InvalidMigrationsFileException('Cannot find `'.$filePath.'` file migrations.');
        }
        if (false === ($fileContent = @file_get_contents($filePath))) {
            throw new InvalidMigrationsFileException('Cannot read `'.$filePath.'` file migrations.');
        }
        if (false === ($decodedContent = @json_decode($filePath, true))) {
            throw new InvalidMigrationsFileException('Cannot parse `'.$filePath.'` file migrations.');
        }

        return $decodedContent;
    }

    /**
     * @param  array                          $versions
     * @throws InvalidMigrationsFileException
     */
    private function writeMigrationFile(array $versions)
    {
        $filePath = $this->getMigrationsDirectory().'/'.$this->getMigrationsFileName();
        if (!file_exists($filePath)) {
            throw new InvalidMigrationsFileException('Cannot find `'.$filePath.'` file migrations.');
        }
        if (false === ($codedContent = @json_encode($versions))) {
            throw new InvalidMigrationsFileException('Cannot parse datas for file migrations.');
        }
        if (false === @file_put_contents($filePath, $codedContent)) {
            throw new InvalidMigrationsFileException('Cannot write datas in `'.$filePath.'` file migrations.');
        }
    }

    /*
     * Getters / Setters
     */

    /**
     * @return string
     */
    public function getMigrationsDirectory()
    {
        return $this->migrationsDirectory;
    }

    /**
     * @param string $migrationsDirectory
     */
    public function setMigrationsDirectory($migrationsDirectory)
    {
        $this->migrationsDirectory = $migrationsDirectory;
    }

    /**
     * @return AbstractMigration[]
     */
    public function getMigrationsList()
    {
        return $this->migrationsList;
    }

    /**
     * @param AbstractMigration[] $migrationsList
     */
    public function setMigrationsList($migrationsList)
    {
        $this->migrationsList = $migrationsList;
    }

    /**
     * @return string
     */
    public function getMigrationsNamespace()
    {
        return $this->migrationsNamespace;
    }

    /**
     * @param string $migrationsNamespace
     */
    public function setMigrationsNamespace($migrationsNamespace)
    {
        $this->migrationsNamespace = $migrationsNamespace;
    }

    /**
     * @return boolean
     */
    public function isMigrationsFileCreated()
    {
        return $this->migrationsFileCreated;
    }

    /**
     * @param boolean $migrationsFileCreated
     */
    public function setMigrationsFileCreated($migrationsFileCreated)
    {
        $this->migrationsFileCreated = $migrationsFileCreated;
    }

    /**
     * @return string
     */
    public function getMigrationsFileName()
    {
        return $this->migrationsFileName;
    }

    /**
     * @param string $migrationsFileName
     */
    public function setMigrationsFileName($migrationsFileName)
    {
        $this->migrationsFileName = $migrationsFileName;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
}
