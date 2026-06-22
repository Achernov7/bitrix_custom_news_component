<?php

namespace Company\NewsList\Service;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * Презентационное форматирование данных новости: дата, обрезка анонса,
 * сборка SEF-ссылки на детальную страницу.
 */
class NewsFormatter
{
    public function date(?string $activeFrom): string
    {
        return $activeFrom ? FormatDate('d.m.Y', MakeTimeStamp($activeFrom)) : '';
    }

    public function truncate(string $html, int $limit): string
    {
        $text = strip_tags($html);

        return mb_strlen($text) > $limit
            ? mb_substr($text, 0, $limit - 3) . '...'
            : $text;
    }

    public function detailUrl(string $template, string $code): string
    {
        return str_replace('#ELEMENT_CODE#', rawurlencode($code), $template);
    }

    public function sectionUrl(string $template, string $code): string
    {
        return str_replace('#SECTION_CODE#', rawurlencode($code), $template);
    }
}
