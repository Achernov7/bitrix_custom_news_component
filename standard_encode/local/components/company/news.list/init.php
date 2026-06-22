<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

require_once __DIR__ . '/src/Config/NewsListParams.php';
require_once __DIR__ . '/src/Filter/NewsFilter.php';
require_once __DIR__ . '/src/Repository/NewsRepository.php';
require_once __DIR__ . '/src/Routing/SefRouter.php';
require_once __DIR__ . '/src/Service/NewsFormatter.php';
require_once __DIR__ . '/src/Service/NewsService.php';
require_once __DIR__ . '/src/Service/NewsCache.php';
require_once __DIR__ . '/src/Contract/ComponentAction.php';
require_once __DIR__ . '/src/Dto/ActionContext.php';
require_once __DIR__ . '/src/Action/DetailAction.php';
require_once __DIR__ . '/src/Action/ListAction.php';
