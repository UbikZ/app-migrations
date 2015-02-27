<?php

$buildDir = realpath(dirname(__FILE__)).'/build';
$pharName = "$buildDir/app-migrations.phar";

if (!file_exists($buildDir)) {
    mkdir($buildDir);
}

if (file_exists($pharName)) {
    unlink($pharName);
}

$p = new Phar($pharName);
$p->CompressFiles(Phar::GZ);
$p->setSignatureAlgorithm(Phar::SHA1);
$p->startBuffering();

$dirs = array(
    __DIR__.'/lib'                         =>  '/Galilee\/Migrations/',
    __DIR__.'/vendor/symfony/console'      =>  '/Symfony/',
);

foreach ($dirs as $dir => $filter) {
    $p->buildFromDirectory($dir, $filter);
}
$p->stopBuffering();
$p->setStub(file_get_contents('phar-cli-stub.php'));
