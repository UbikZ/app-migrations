<?php

namespace Ubikz\Migrations\Console\Command;

use Ubikz\Migrations\Configuration\DefaultConfiguration;
use Ubikz\Migrations\Exceptions\InvalidConfigurationFileException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    /** @var  DefaultConfiguration */
    private $configuration;

    public function configure()
    {
        $this->addArgument('configuration', InputArgument::REQUIRED);
    }

    /**
     * @param DefaultConfiguration $configuration
     * @param OutputInterface      $output
     */
    protected function outputHeader(DefaultConfiguration $configuration, OutputInterface $output)
    {
        $name = $configuration->getName();
        $name = $name ? $name : 'Application Database Migrations';
        $name = str_repeat(' ', 20).$name.str_repeat(' ', 20);
        $output->writeln('<question>'.str_repeat(' ', strlen($name)).'</question>');
        $output->writeln('<question>'.$name.'</question>');
        $output->writeln('<question>'.str_repeat(' ', strlen($name)).'</question>');
        $output->writeln('');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return DefaultConfiguration
     *
     * @throws InvalidConfigurationFileException
     */
    protected function getMigrationConfiguration(InputInterface $input, OutputInterface $output)
    {
        if (null === $this->getConfiguration()) {
            $configuration = new DefaultConfiguration();
            if ($filepath = $input->getArgument('configuration')) {
                $info = pathinfo($filepath);
                $configuration->importConfiguration(realpath($filepath), $info['extension']);
            } else {
                throw new InvalidConfigurationFileException('You have to specify a configuration file.');
            }
            $this->configuration = $configuration;
        }

        return $this->getConfiguration();
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
}
