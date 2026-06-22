<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

require_once __DIR__ . '/src/IblockTypeSeeder.php';
require_once __DIR__ . '/src/IblockSeeder.php';
require_once __DIR__ . '/src/SectionSeeder.php';
require_once __DIR__ . '/src/ElementSeeder.php';
require_once __DIR__ . '/src/NewsSeeder.php';

return new NewsSeeder(
    __DIR__ . '/data',
    new IblockTypeSeeder(),
    new IblockSeeder(),
    new SectionSeeder(),
    new ElementSeeder(),
);
