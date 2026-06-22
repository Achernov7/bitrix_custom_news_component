<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

return [
    'ACTIVE'         => 'Y',
    'NAME'           => 'Кастомные новости',
    'CODE'           => 'custom_news',
    'IBLOCK_TYPE_ID' => 'content',
    'SITE_ID'        => ['s1'],
    'VERSION'        => 2,
    'INDEX_ELEMENT'  => 'N',
    'INDEX_SECTION'  => 'N',
    'GROUP_ID'       => ['2' => 'R'],  // Все пользователи — чтение
];
