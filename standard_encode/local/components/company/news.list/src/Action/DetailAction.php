<?php

namespace Company\NewsList\Action;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Company\NewsList\Contract\ComponentAction;
use Company\NewsList\Dto\ActionContext;

/**
 * Детальная страница новости: выборка по коду (с кешированием), 404,
 * заголовок/мета, хлебные крошки, рендер шаблона detail.
 */
final class DetailAction implements ComponentAction
{
    public function __construct(private readonly ActionContext $context) {}

    public function run(): void
    {
        global $APPLICATION;

        $c = $this->context;
        $code = $c->router->variable('ELEMENT_CODE');

        $element = $code === '' ? null : $c->cache->remember(
            'detail:' . $c->iblockId . ':' . md5($code),
            '/local/company.news.list/detail',
            fn () => $c->service->getDetail($code, $c->sections, $c->router->sectionUrlTemplate())
        );

        if (!$element) {
            \Bitrix\Iblock\Component\Tools::process404(
                'Новость не найдена',
                $c->params->setStatus404,
                $c->params->setStatus404,
                false, ''
            );
            return;
        }

        $arResult = $c->router->toArResult();
        $arResult['ELEMENT'] = $element;

        if ($c->params->setTitle) {
            $APPLICATION->SetTitle(htmlspecialcharsbx($element['~NAME']));
        }
        if ($c->params->setBrowserTitle) {
            $APPLICATION->SetPageProperty('title', htmlspecialcharsbx($element['~NAME']));
        }

        // Корень крошек («Новости») Битрикс берёт из /news/.section.php.
        if (!empty($element['SECTION'])) {
            $APPLICATION->AddChainItem(
                htmlspecialcharsbx($element['SECTION']['NAME']),
                $element['SECTION']['URL']
            );
        }
        $APPLICATION->AddChainItem(htmlspecialcharsbx($element['~NAME']));

        $c->component->arResult = $arResult;
        $c->component->includeComponentTemplate('detail');
    }
}
