<?php

Phar::mapPhar();

require_once 'phar://'.__FILE__.'/Galilee/Migrations/ClassLoader.php';

$classLoader = new \Galilee\Migrations\ClassLoader('Symfony', 'phar://'.__FILE__);
$classLoader->register();

$classLoader = new \Galilee\Migrations\ClassLoader('Galilee\Migrations', 'phar://'.__FILE__);
$classLoader->register();

$helperSet = new \Symfony\Component\Console\Helper\HelperSet();
$helperSet->set(new \Symfony\Component\Console\Helper\DialogHelper(), 'dialog');
$cli = new \Symfony\Component\Console\Application('Application Migrations', '0.0.1');
$cli->setCatchExceptions(true);
$cli->setHelperSet($helperSet);
$cli->addCommands(array(
    new \Galilee\Migrations\Console\Command\GenerateCommand(),
    new \Galilee\Migrations\Console\Command\MigrateCommand(),
));
$cli->run();

__HALT_COMPILER();
