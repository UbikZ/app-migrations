<?php

Phar::mapPhar();

require_once 'phar://'.__FILE__.'/Ubikz/Migrations/ClassLoader.php';

$classLoader = new \Ubikz\Migrations\ClassLoader('Symfony', 'phar://'.__FILE__);
$classLoader->register();

$classLoader = new \Ubikz\Migrations\ClassLoader('Ubikz\Migrations', 'phar://'.__FILE__);
$classLoader->register();

$helperSet = new \Symfony\Component\Console\Helper\HelperSet();
$helperSet->set(new \Symfony\Component\Console\Helper\DialogHelper(), 'dialog');
$cli = new \Symfony\Component\Console\Application('Application Migrations', '0.0.1');
$cli->setCatchExceptions(true);
$cli->setHelperSet($helperSet);
$cli->addCommands(array(
    new \Ubikz\Migrations\Console\Command\GenerateCommand(),
    new \Ubikz\Migrations\Console\Command\MigrateCommand(),
    new \Ubikz\Migrations\Console\Command\StatusCommand(),
));
$cli->run();

__HALT_COMPILER();
