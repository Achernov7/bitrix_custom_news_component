<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

if (!Loader::includeModule('iblock')) return;

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arIBlock = [];
$rsIBlock = CIBlock::GetList(['SORT' => 'ASC'], ['ACTIVE' => 'Y']);
while ($arr = $rsIBlock->Fetch()) {
    $arIBlock[$arr['ID']] = '[' . $arr['ID'] . '] ' . $arr['NAME'];
}

$arComponentParameters = [
    'PARAMETERS' => [
        'IBLOCK_TYPE' => [
            'PARENT'  => 'BASE',
            'NAME'    => 'Тип инфоблока',
            'TYPE'    => 'LIST',
            'VALUES'  => $arIBlockType,
            'DEFAULT' => 'content',
            'REFRESH' => 'Y',
        ],
        'IBLOCK_ID' => [
            'PARENT'           => 'BASE',
            'NAME'             => 'Инфоблок',
            'TYPE'             => 'LIST',
            'VALUES'           => $arIBlock,
            'ADDITIONAL_VALUES' => 'Y',
            'REFRESH'          => 'Y',
        ],
        'NEWS_COUNT' => [
            'PARENT'  => 'BASE',
            'NAME'    => 'Новостей на странице',
            'TYPE'    => 'STRING',
            'DEFAULT' => '10',
        ],
        'SET_TITLE' => [
            'PARENT'  => 'ADDITIONAL_SETTINGS',
            'NAME'    => 'Устанавливать заголовок страницы',
            'TYPE'    => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ],
        'SET_BROWSER_TITLE' => [
            'PARENT'  => 'ADDITIONAL_SETTINGS',
            'NAME'    => 'Устанавливать заголовок окна браузера',
            'TYPE'    => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ],
        'SET_STATUS_404' => [
            'PARENT'  => 'ADDITIONAL_SETTINGS',
            'NAME'    => 'Устанавливать 404 для несуществующих',
            'TYPE'    => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ],
        'CACHE_TYPE' => [
            'PARENT'  => 'CACHE_SETTINGS',
            'NAME'    => 'Тип кеширования',
            'TYPE'    => 'LIST',
            'VALUES'  => ['A' => 'Авто', 'Y' => 'Кешировать', 'N' => 'Не кешировать'],
            'DEFAULT' => 'A',
        ],
        'CACHE_TIME' => [
            'PARENT'  => 'CACHE_SETTINGS',
            'NAME'    => 'Время кеширования (сек.)',
            'TYPE'    => 'STRING',
            'DEFAULT' => 3600,
        ],
        'CACHE_FILTER' => [
            'PARENT'  => 'CACHE_SETTINGS',
            'NAME'    => 'Кешировать при установленном фильтре',
            'TYPE'    => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ],
        'SEF_MODE' => [
            'news'   => ['NAME' => 'Список новостей',  'DEFAULT' => '',                      'VARIABLES' => []],
            'detail' => ['NAME' => 'Детальная страница', 'DEFAULT' => '#ELEMENT_CODE#/',     'VARIABLES' => ['ELEMENT_CODE']],
            'section'=> ['NAME' => 'Список по категории','DEFAULT' => 'category/#SECTION_CODE#/', 'VARIABLES' => ['SECTION_CODE']],
        ],
    ],
];
