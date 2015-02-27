<?php

namespace Galilee\Migrations;

use Galilee\Migrations\Configuration\DefaultConfiguration;
use Galilee\Migrations\Tools\OutputWriter;

class Migration
{
    /** @var  OutputWriter */
    private $outputWriter;

    /** @var  DefaultConfiguration */
    private $configuration;

    public function __construct(DefaultConfiguration $configuration)
    {
        $this->setOutputWriter($configuration->getOutputWriter());
        $this->setConfiguration($configuration);
    }

    /*
     * Getters / Setters
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
}
