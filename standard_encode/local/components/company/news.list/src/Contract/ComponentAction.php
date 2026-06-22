<?php

namespace Company\NewsList\Contract;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * Экшен страницы компонента: сам наполняет $arResult и подключает шаблон
 * (либо отдаёт 404). Вызывается контроллером после маршрутизации.
 */
interface ComponentAction
{
    public function run(): void;
}
