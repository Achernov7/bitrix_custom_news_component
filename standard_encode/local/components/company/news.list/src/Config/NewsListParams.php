<?php

namespace Company\NewsList\Config;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * Нормализованные и типизированные параметры компонента.
 * Вся логика приведения значений из массива $arParams собрана здесь.
 */
class NewsListParams
{
    public readonly string $iblockType;
    public readonly string|int $rawIblockId;
    public readonly int $newsCount;
    public readonly string $sefFolder;
    public readonly string $cacheType;
    public readonly int $cacheTime;
    public readonly bool $cacheFilter;
    public readonly bool $setTitle;
    public readonly bool $setBrowserTitle;
    public readonly bool $setStatus404;
    public readonly array $sefUrlTemplates;

    public function __construct(array $p)
    {
        $this->iblockType      = trim((string)($p['IBLOCK_TYPE'] ?? 'content'));
        $this->rawIblockId     = $p['IBLOCK_ID'] ?? 0;
        $this->newsCount       = max(1, (int)($p['NEWS_COUNT'] ?? 10));
        $this->sefFolder       = '/' . trim((string)($p['SEF_FOLDER'] ?? '/news/'), '/') . '/';
        $this->cacheType       = trim((string)($p['CACHE_TYPE'] ?? 'A'));
        $this->cacheTime       = (int)($p['CACHE_TIME'] ?? 3600);
        $this->cacheFilter     = ($p['CACHE_FILTER'] ?? 'Y') !== 'N';
        $this->setTitle        = ($p['SET_TITLE'] ?? 'Y') !== 'N';
        $this->setBrowserTitle = ($p['SET_BROWSER_TITLE'] ?? 'Y') !== 'N';
        $this->setStatus404    = ($p['SET_STATUS_404'] ?? 'Y') !== 'N';
        $this->sefUrlTemplates = (array)($p['SEF_URL_TEMPLATES'] ?? []);
    }

    /** TTL результата компонента: 0 — кеширование отключено. */
    public function cacheTtl(): int
    {
        return $this->cacheType === 'N' ? 0 : $this->cacheTime;
    }
}
