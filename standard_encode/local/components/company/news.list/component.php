<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @global CMain $APPLICATION */
/** @var CBitrixComponent $this */
/** @var array $arParams */

use Bitrix\Main\Loader;
use Company\NewsList\Action\DetailAction;
use Company\NewsList\Action\ListAction;
use Company\NewsList\Config\NewsListParams;
use Company\NewsList\Dto\ActionContext;
use Company\NewsList\Filter\NewsFilter;
use Company\NewsList\Repository\NewsRepository;
use Company\NewsList\Routing\SefRouter;
use Company\NewsList\Service\NewsCache;
use Company\NewsList\Service\NewsFormatter;
use Company\NewsList\Service\NewsService;

if (!Loader::includeModule('iblock')) {
    ShowError('Модуль iblock не установлен');
    return;
}

require_once __DIR__ . '/init.php';

// ─── Bootstrap ──────────────────────────────────────────────────────────────

$params = new NewsListParams($arParams);

$repository = NewsRepository::locate($params->iblockType, $params->rawIblockId);
if (!$repository) {
    ShowError('Инфоблок news не найден. Укажите корректный IBLOCK_ID.');
    return;
}

$router = new SefRouter($this, $params);
$router->resolve();

$service = new NewsService($repository, new NewsFormatter());
$context = new ActionContext(
    component: $this,
    params:    $params,
    service:   $service,
    cache:     new NewsCache($params->cacheTtl()),
    router:    $router,
    sections:  $service->getSections(),
    request:   $_GET,
    iblockId:  $repository->iblockId(),
);

// ─── Dispatch ───────────────────────────────────────────────────────────────

$action = $router->page() === 'detail'
    ? new DetailAction($context)
    : new ListAction($context, new NewsFilter($_GET));

$action->run();
