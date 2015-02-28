<?php

namespace Galilee\Migrations;

use Galilee\Migrations\Configuration\DefaultConfiguration;
use Galilee\Migrations\Exceptions\InvalidMigrationsVersionException;
use Galilee\Migrations\Tools\OutputWriter;
use Symfony\Component\Console\Output\OutputInterface;

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

    /**
     * @param null $to
     * @param OutputInterface $output
     * @return array
     * @throws InvalidMigrationsVersionException
     * @throws \Exception
     */
    public function migrate($to = null, OutputInterface $output)
    {
        if ($to === null) {
            $to = $this->configuration->getLatestVersion();
        }
        $from = $this->configuration->getCurrentVersion();
        $from = (string) $from;
        $to = (string) $to;

        $migrations = $this->configuration->getMigrationsList();
        if (! isset($migrations[$to]) && $to > 0) {
            throw new InvalidMigrationsVersionException('Try to migrate to a not found version `'.$to.'`.');
        }
        $direction = $from > $to ? 'down' : 'up';
        $migrationsToExecute = $this->configuration->getMigrationsToExecute($direction, $to);
        if ($from === $to && empty($migrationsToExecute) && $migrations) {
            return array();
        }

        $output->writeln(sprintf('Migrating <info>%s</info> to <comment>%s</comment> from <comment>%s</comment>', $direction, $to, $from));
        if (empty($migrationsToExecute)) {
            throw new InvalidMigrationsVersionException('No migration to execute.');
        }
        $requests = array();
        $time = 0;
        /** @var Version $version */
        foreach ($migrationsToExecute as $version) {
            $request = $version->execute($direction, $output);
            $requests[$version->getVersion()] = $request;
            $time += $version->getExecuteTime();
        }
        $output->writeln("\n  <comment>------------------------</comment>\n");
        $output->writeln(sprintf("  <info>++</info> finished in %s", $time));
        $output->writeln(sprintf("  <info>++</info> %s migrations executed", count($migrationsToExecute)));

        return $requests;
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
