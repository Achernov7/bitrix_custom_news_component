<?php

namespace Company\NewsList\Routing;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Company\NewsList\Config\NewsListParams;
use CBitrixComponent;
use CComponentEngine;

/**
 * SEF-маршрутизация компонента поверх CComponentEngine: определяет текущую
 * страницу (news / detail / section), разбирает переменные URL и готовит
 * базовую часть $arResult для шаблонов.
 */
class SefRouter
{
    private const DEFAULT_TEMPLATES = [
        'news'    => '',
        'detail'  => '#ELEMENT_CODE#/',
        'section' => 'category/#SECTION_CODE#/',
    ];
    private const VARIABLES = ['ELEMENT_CODE', 'SECTION_CODE'];

    private string $page = 'news';
    private array $variables = [];
    private array $urlTemplates = [];

    public function __construct(
        private readonly CBitrixComponent $component,
        private readonly NewsListParams $params,
    ) {}

    public function resolve(): void
    {
        // Дефолты + переопределения из SEF_URL_TEMPLATES (custom перекрывает default).
        // возможно лишнее
        $this->urlTemplates = CComponentEngine::makeComponentUrlTemplates(
            self::DEFAULT_TEMPLATES,
            $this->params->sefUrlTemplates
        );

        // guessComponentPath возвращает имя страницы, а распарсенные переменные
        // (#ELEMENT_CODE# / #SECTION_CODE#) пишет в $this->variables по ссылке.
        $engine = new CComponentEngine($this->component);
        $this->page = $engine->guessComponentPath(
            $this->params->sefFolder,
            $this->urlTemplates,
            $this->variables
        ) ?: 'news';

    }

    public function page(): string
    {
        return $this->page;
    }

    public function variable(string $name): string
    {
        return (string)($this->variables[$name] ?? '');
    }

    public function detailUrlTemplate(): string
    {
        return $this->params->sefFolder . ($this->urlTemplates['detail'] ?? '#ELEMENT_CODE#/');
    }

    public function sectionUrlTemplate(): string
    {
        return $this->params->sefFolder . ($this->urlTemplates['section'] ?? 'category/#SECTION_CODE#/');
    }

    /** Готовая SEF-ссылка на список конкретной категории. */
    public function sectionUrl(string $code): string
    {
        return str_replace('#SECTION_CODE#', rawurlencode($code), $this->sectionUrlTemplate());
    }

    /** Базовая часть $arResult, общая для всех страниц. */
    public function toArResult(): array
    {
        return [
            'FOLDER' => $this->params->sefFolder,
        ];
    }
}
