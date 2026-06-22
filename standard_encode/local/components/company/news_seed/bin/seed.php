#!/usr/bin/env php
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$_SERVER['DOCUMENT_ROOT'] = '/var/www/html';
$_SERVER['HTTP_HOST']     = 'localhost';
$_SERVER['SERVER_NAME']   = 'localhost';
$_SERVER['DOCUMENT_URI']  = '/';

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('B_PROLOG_INCLUDED', true);

require_once '/var/www/html/bitrix/modules/main/include/prolog_before.php';

$seeder = require __DIR__ . '/../init.php';

foreach ($seeder->run() as $line) {
    echo $line . PHP_EOL;
}
