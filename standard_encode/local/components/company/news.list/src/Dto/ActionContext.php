<?php

namespace Company\NewsList\Dto;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Company\NewsList\Config\NewsListParams;
use Company\NewsList\Routing\SefRouter;
use Company\NewsList\Service\NewsCache;
use Company\NewsList\Service\NewsService;
use CBitrixComponent;

/**
 * Зависимости, общие для всех экшенов. Собирается контроллером (component.php)
 * один раз и передаётся в нужный экшен.
 */
final class ActionContext
{
    public function __construct(
        public readonly CBitrixComponent $component,
        public readonly NewsListParams $params,
        public readonly NewsService $service,
        public readonly NewsCache $cache,
        public readonly SefRouter $router,
        public readonly array $sections,
        public readonly array $request,
        public readonly int $iblockId,
    ) {}
}
