<?php

defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'testing'));
define('ROOT_PATH', __DIR__.'/..');
define('BUILD_PATH', ROOT_PATH.'/build');
define('LOGS_PATH', ROOT_PATH.'/logs');
define('VENDOR_PATH', ROOT_PATH.'/vendor');

if (file_exists($path = VENDOR_PATH.'/autoload.php')) {
    require $path;
}
