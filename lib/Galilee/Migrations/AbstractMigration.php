<?php

namespace Galilee\Migrations;

use Galilee\Migrations\Configuration\DefaultConfiguration;
use Galilee\Migrations\Tools\OutputWriter;

abstract class AbstractMigration
{
    /** @var  DefaultConfiguration */
    private $configuration;

    /** @var  OutputWriter */
    private $outputWriter;

    /** @var Version */
    protected $version;

    public function __construct(Version $version)
    {
        $this->setConfiguration($version->getConfiguration());
        $this->setOutputWriter($this->getConfiguration()->getOutputWriter());
        $this->setVersion($version);
    }

    /**
     * @return mixed
     */
    abstract public function up(/* Mon ORM */);

    /**
     * @return mixed
     */
    abstract public function down(/* Mon ORM */);

    protected function write($message)
    {
        $this->getOutputWriter()->write($message);
    }

    public function preUp(/* Mon ORM */)
    {
    }

    public function postUp(/* Mon ORM */)
    {
    }

    public function preDown(/* Mon ORM */)
    {
    }

    public function postDown(/* Mon ORM */)
    {
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
     * @return Version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param Version $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }
}
