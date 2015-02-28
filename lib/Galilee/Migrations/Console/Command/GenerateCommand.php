<?php

namespace Galilee\Migrations\Console\Command;

use Galilee\Migrations\Configuration\DefaultConfiguration;
use Galilee\Migrations\Exceptions\InvalidMigrationsClassException;
use Galilee\Migrations\Exceptions\InvalidMigrationsDirectoryException;
use Galilee\Migrations\Exceptions\InvalidMigrationsFileException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GenerateCommand.
 */
class GenerateCommand extends AbstractCommand
{
    public function configure()
    {
        $this
            ->setName('migrations:generate')
            ->setDescription('Generate a blank migration class');
        parent::configure();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     *
     * @throws InvalidMigrationsClassException
     * @throws InvalidMigrationsDirectoryException
     * @throws InvalidMigrationsFileException
     * @throws \Galilee\Migrations\Exceptions\InvalidConfigurationFileException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = $this->getMigrationConfiguration($input, $output);
        $version = date('YmdHis');
        $path = $this->generateMigration($configuration, $version);
        $output->writeln(sprintf('Generated new migration class to "<info>%s</info>"', $path));
    }

    /**
     * @param DefaultConfiguration $configuration
     * @param $version
     * @param array                $uses
     *
     * @return string
     *
     * @throws InvalidMigrationsClassException
     * @throws InvalidMigrationsDirectoryException
     * @throws InvalidMigrationsFileException
     */
    public function generateMigration(DefaultConfiguration $configuration, $version, array $uses = array())
    {
        if (false === ($template = @file_get_contents(__DIR__.'/tmpl/migration_class'))) {
            throw new InvalidMigrationsClassException('Cannot find template class.');
        }
        $placeHolders = array('<namespace>', '<uses>', '<version>');
        array_walk($uses, function (&$item, $key, $value) {
            $item = sprintf('use %s;', $value);
        });
        $replacements = array($configuration->getMigrationsNamespace(), implode(PHP_EOL, $uses), $version);
        $code = str_replace($placeHolders, $replacements, $template);
        $code = preg_replace('/^ +$/m', '', $code);
        $dir = $configuration->getMigrationsDirectory() ?: getcwd();
        $path = rtrim($dir, '/').'/Version'.$version.'.php';

        if (!file_exists($dir)) {
            throw new InvalidMigrationsDirectoryException('Cannot found `'.$dir.'` migrations directory.');
        }

        if (false === @file_put_contents($path, $code)) {
            throw new InvalidMigrationsFileException('Cannot create template `'.$path.'` file migrations.');
        }

        return $path;
    }
}
