<?php

namespace Galilee\Migrations\Console\Command;

use Galilee\Migrations\Configuration\DefaultConfiguration;
use Galilee\Migrations\Exceptions\InvalidMigrationsClassException;
use Galilee\Migrations\Exceptions\InvalidMigrationsDirectoryException;
use Galilee\Migrations\Exceptions\InvalidMigrationsFileException;
use Galilee\Migrations\Migration;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GenerateCommand
 * @package Galilee\Migrations\Console\Command
 */
class MigrateCommand extends AbstractCommand
{
    public function configure()
    {
        parent::configure();

        $this
            ->setName('migrations:migrate')
            ->setDescription('Execute a migration to a specified version or the latest available one.')
            ->addArgument('version', InputArgument::OPTIONAL, 'The version number (YYYYMMDDHHMMSS) or alias (first, prev, next, latest) to migrate to.', 'latest');
    }

    /**
     * @param  InputInterface                                                   $input
     * @param  OutputInterface                                                  $output
     * @return int|null|void
     * @throws InvalidMigrationsClassException
     * @throws InvalidMigrationsDirectoryException
     * @throws InvalidMigrationsFileException
     * @throws \Galilee\Migrations\Exceptions\InvalidConfigurationFileException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = $this->getMigrationConfiguration($input, $output);
        $migration = new Migration($configuration);

        $this->outputHeader($configuration, $output);
        $noInteraction = !$input->isInteractive();

        $executedMigrations = $configuration->getMigratedVersions();
        $availableMigrations = $configuration->getAvailableVersions();
        $executedUnavailableMigrations = array_diff($executedMigrations, $availableMigrations);

        $versionAlias = $input->getArgument('version');
        $version = $configuration->resolveVersionAlias($versionAlias);
        if ($version === null) {
            switch ($versionAlias) {
                case 'prev':
                    $output->writeln('<error>Already at first version.</error>');
                    break;
                case 'next':
                    $output->writeln('<error>Already at latest version.</error>');
                    break;
                default:
                    $output->writeln('<error>Unknown version: ' . $output->getFormatter()->escape($versionAlias) . '</error>');
            }
            return 1;
        }

        if ($executedUnavailableMigrations) {
            $output->writeln(sprintf('<error>WARNING! You have %s previously executed migrations in the database that are not registered migrations.</error>', count($executedUnavailableMigrations)));
            foreach ($executedUnavailableMigrations as $executedUnavailableMigration) {
                $output->writeln('    <comment>>></comment> ' . $configuration->formatVersion($executedUnavailableMigration) . ' (<comment>' . $executedUnavailableMigration . '</comment>)');
            }
            if (!$noInteraction) {
                $confirmation = $this->getHelper('dialog')->askConfirmation($output, '<question>Are you sure you wish to continue? (y/n)</question>', false);
                if (! $confirmation) {
                    $output->writeln('<error>Migration cancelled!</error>');
                    return 1;
                }
            }
        }

        $sql = $migration->migrate($version);
        if (! $sql) {
            $output->writeln('<comment>No migrations to execute.</comment>');
        }
    }
}
