<?php

namespace Company\NewsList\Action;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Company\NewsList\Contract\ComponentAction;
use Company\NewsList\Dto\ActionContext;
use Company\NewsList\Filter\NewsFilter;

/**
 * Список новостей: фильтр из GET, постраничная навигация, кеш по
 * фильтру + странице + группам пользователя, рендер шаблона news.
 */
final class ListAction implements ComponentAction
{
    public function __construct(
        private readonly ActionContext $context,
        private readonly NewsFilter $filter,
    ) {}

    public function run(): void
    {
        global $USER, $APPLICATION;

        $c = $this->context;
        $filter = $this->filter;
        $page = max(1, (int)($c->request['PAGEN_1'] ?? 1));

        // SEF-страница категории: /news/category/CODE/ — раздел задаётся URL-путём.
        $section = $this->resolveSection($filter);
        if ($section === false) {
            return; // 404 уже отдан
        }

        $arResult = $c->router->toArResult();
        $arResult['FILTER']        = $filter->getRaw();
        $arResult['FILTER_ERROR']  = $filter->getError();
        $arResult['SECTION']       = $section;
        $arResult['LIST_URL']      = $section
            ? $c->router->sectionUrl((string)$section['CODE'])
            : $arResult['FOLDER'];

        // Заголовок страницы: «Новости» для списка, «Новости: Категория» для раздела.
        $pageTitle = $section
            ? 'Новости: ' . htmlspecialcharsbx($section['NAME'])
            : 'Новости';
        if ($c->params->setTitle) {
            $APPLICATION->SetTitle($pageTitle);
        }
        if ($c->params->setBrowserTitle) {
            $APPLICATION->SetPageProperty('title', $pageTitle);
        }
        // Корень крошек («Новости») Битрикс берёт из /news/.section.php — здесь только текущая категория.
        if ($section) {
            $APPLICATION->AddChainItem(htmlspecialcharsbx($section['NAME']));
        }

        // Cache key: iblock + СЫРЫЕ значения фильтра + page + размер страницы + группы пользователя.
        // Важно: ключ строим на getRaw(), а НЕ на toConditions(). В кешируемый результат
        // входит NAV_STRING, чьи ссылки содержат текущую query-строку (year/category/даты).
        // toConditions() при заданных датах отбрасывает year (приоритет дат), поэтому запросы
        // с разным year и одинаковыми датами схлопнулись бы в один ключ — и в пагинацию вмёрз
        // бы year первого запроса. getRaw() однозначно определяет и условия, и вид URL.
        $cacheId = md5(
            $c->iblockId
            . ':' . serialize($filter->getRaw())
            . ':p' . $page
            . ':n' . $c->params->newsCount
            . ':g' . implode(',', $USER->GetUserGroupArray())
        );

        $producer = function () use ($c, $filter, $page) {
            $list = $c->service->getList(
                $filter,
                ['nPageSize' => $c->params->newsCount, 'iNumPage' => $page],
                ['title' => '', 'template' => '', 'showAlways' => false, 'parent' => $c->component],
                $c->router->detailUrlTemplate(),
                $c->router->sectionUrlTemplate(),
                $c->sections
            );

            return [
                'ITEMS'      => $list['items'],
                'NAV_STRING' => $list['navString'],
                'YEARS'      => $c->service->getYears(),
                'SECTIONS'   => array_values($c->sections),
            ];
        };

        // CACHE_FILTER=N: при активном фильтре кеш не используем (стандартная семантика Битрикс).
        $filterActive = $filter->toConditions() !== [];
        $useCache = $c->params->cacheTtl() > 0 && ($c->params->cacheFilter || !$filterActive);

        $data = $useCache
            ? $c->cache->remember($cacheId, '/local/company.news.list', $producer)
            : $producer();

        $arResult = array_merge($arResult, $data);

        $c->component->arResult = $arResult;
        $c->component->includeComponentTemplate('news');
    }

    /**
     * Разрешает раздел из SEF-пути и жёстко задаёт его в фильтре.
     *
     * @return array|null|false раздел; null — обычный список; false — отдан 404
     */
    private function resolveSection(NewsFilter $filter): array|null|false
    {
        $c = $this->context;
        $code = $c->router->variable('SECTION_CODE');
        if ($code === '') {
            return null;
        }

        $section = $c->service->getSectionByCode($code);
        if (!$section) {
            \Bitrix\Iblock\Component\Tools::process404(
                'Категория не найдена',
                $c->params->setStatus404,
                $c->params->setStatus404,
                false, ''
            );
            return false;
        }

        $filter->forceCategory((int)$section['ID']);
        return $section;
    }
}
