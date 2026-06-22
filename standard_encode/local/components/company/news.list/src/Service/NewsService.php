<?php

namespace Company\NewsList\Service;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Company\NewsList\Filter\NewsFilter;
use Company\NewsList\Repository\NewsRepository;

/**
 * Бизнес-логика новостей: связывает репозиторий и форматтер, отдаёт
 * шаблонам полностью готовые к выводу структуры данных.
 */
class NewsService
{
    public function __construct(
        private readonly NewsRepository $repository,
        private readonly NewsFormatter $formatter,
    ) {}

    public function getSections(): array
    {
        return $this->repository->getSections();
    }

    public function getSectionByCode(string $code): ?array
    {
        return $this->repository->findSectionByCode($code);
    }

    public function getYears(): array
    {
        return $this->repository->getActiveYears();
    }

    /**
     * @return array{items: array, nav: array{page: int, pages: int, total: int}, navString: string}
     */
    public function getList(NewsFilter $filter, array $nav, array $pager, string $detailUrlTemplate, string $sectionUrlTemplate, array $sections): array
    {
        $raw = $this->repository->findList($filter->toConditions(), $nav, $pager);

        return [
            'items'     => array_map(
                fn (array $row) => $this->decorateItem($row, $sections, $detailUrlTemplate, $sectionUrlTemplate),
                $raw['rows']
            ),
            'navString' => $raw['navString'],
        ];
    }

    public function getDetail(string $code, array $sections, string $sectionUrlTemplate): ?array
    {
        $element = $this->repository->findByCode($code);
        if (!$element) {
            return null;
        }

        $section = $sections[(int)$element['IBLOCK_SECTION_ID']] ?? null;
        if ($section) {
            $section['URL'] = $this->formatter->sectionUrl($sectionUrlTemplate, (string)$section['CODE']);
        }

        $element['SECTION'] = $section;
        $element['DATE_ACTIVE_FROM'] = $this->formatter->date($element['~ACTIVE_FROM']);

        return $element;
    }

    private function decorateItem(array $row, array $sections, string $detailUrlTemplate, string $sectionUrlTemplate): array
    {
        $section = $sections[(int)$row['IBLOCK_SECTION_ID']] ?? null;
        if ($section) {
            $section['URL'] = $this->formatter->sectionUrl($sectionUrlTemplate, (string)$section['CODE']);
        }

        $row['SECTION'] = $section;
        $row['DATE_ACTIVE_FROM'] = $this->formatter->date($row['~ACTIVE_FROM']);
        $row['PREVIEW_TEXT_SHORT'] = $this->formatter->truncate((string)$row['~PREVIEW_TEXT'], 200);
        $row['DETAIL_URL'] = $this->formatter->detailUrl($detailUrlTemplate, (string)$row['~CODE']);

        return $row;
    }
}
