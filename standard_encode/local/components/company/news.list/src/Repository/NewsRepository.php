<?php

namespace Company\NewsList\Repository;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Application;
use CIBlock;
use CIBlockElement;
use CIBlockSection;

/**
 * Доступ к данным инфоблока новостей. Возвращает «сырые» строки БД —
 * форматированием и сборкой DTO занимается сервисный слой.
 */
class NewsRepository
{
    private function __construct(private int $iblockId) {}

    /**
     * Фабрика: принимает ID или символьный код инфоблока.
     * Возвращает null, если инфоблок не найден.
     */
    public static function locate(string $type, string|int $idOrCode): ?self
    {
        if (is_numeric($idOrCode) && (int)$idOrCode > 0) {
            return new self((int)$idOrCode);
        }

        $code = (string)$idOrCode;
        foreach ([['TYPE' => $type, 'CODE' => $code], ['CODE' => $code]] as $filter) {
            if ($row = CIBlock::GetList([], $filter + ['ACTIVE' => 'Y'])->Fetch()) {
                return new self((int)$row['ID']);
            }
        }

        return null;
    }

    public function iblockId(): int
    {
        return $this->iblockId;
    }

    /** Активные разделы инфоблока: [ID => ['ID', 'NAME', 'CODE']]. */
    public function getSections(): array
    {
        $sections = [];
        $rs = CIBlockSection::GetList(
            ['SORT' => 'ASC'],
            ['IBLOCK_ID' => $this->iblockId, 'ACTIVE' => 'Y'],
            false,
            ['ID', 'NAME', 'CODE']
        );
        while ($section = $rs->Fetch()) {
            $sections[(int)$section['ID']] = $section;
        }
        return $sections;
    }

    /**
     * Список новостей с постраничной навигацией.
     *
     * @param array $conditions дополнительные условия фильтра (год / дата / раздел)
     * @param array $pager      опции стандартного пейджера (title / template / showAlways)
     * @return array{rows: array, navString: string, nav: array{page: int, pages: int, total: int}}
     */
    public function findList(array $conditions, array $nav, array $pager = []): array
    {
        $filter = array_merge([
            'IBLOCK_ID'   => $this->iblockId,
            'ACTIVE'      => 'Y',
            'CHECK_DATES' => 'Y',
        ], $conditions);

        $rs = CIBlockElement::GetList(
            ['ACTIVE_FROM' => 'DESC', 'SORT' => 'ASC'],
            $filter,
            false,
            $nav,
            ['ID', 'NAME', 'CODE', 'ACTIVE_FROM', 'PREVIEW_TEXT', 'PREVIEW_PICTURE', 'IBLOCK_SECTION_ID']
        );

        // GetNext() — стандартный подход Битрикс: каждое поле возвращается в двух вариантах:
        // NAME (прошло через htmlspecialcharsEx, безопасно для прямого вывода в HTML) и
        // ~NAME (сырое значение — для SetTitle/AddChainItem/truncate и т.п.).
        $rows = [];
        while ($row = $rs->GetNext()) {
            $rows[] = $row;
        }

        // Стандартная постраничная навигация Битрикс (bitrix:system.pagenavigation).
        // Ссылки страниц строятся от текущего URL + $_GET без PAGEN_*, поэтому
        // SEF-путь и активные GET-фильтры сохраняются в пагинации автоматически.
        // $pager['parent'] — родительский компонент для bitrix:system.pagenavigation
        // (стандартная практика; используется штатный шаблон навигации .default).
        $navComponentObject = null;
        $navString = $rs->GetPageNavStringEx(
            $navComponentObject,
            $pager['title'] ?? '',
            $pager['template'] ?? '',
            $pager['showAlways'] ?? false,
            $pager['parent'] ?? null
        );

        return [
            'rows'      => $rows,
            'navString' => $navString,
            'nav'  => [
                'page'  => (int)$rs->NavPageNomer,
                'pages' => max(1, (int)$rs->NavPageCount),
                'total' => (int)$rs->NavRecordCount,
            ],
        ];
    }

    /** Активный раздел по символьному коду или null. */
    public function findSectionByCode(string $code): ?array
    {
        $rs = CIBlockSection::GetList(
            [],
            [
                'IBLOCK_ID' => $this->iblockId,
                'CODE'      => $code,
                'ACTIVE'    => 'Y',
            ],
            false,
            ['ID', 'NAME', 'CODE']
        );

        return $rs->Fetch() ?: null;
    }

    /** Активный элемент по символьному коду или null. */
    public function findByCode(string $code): ?array
    {
        $rs = CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID'   => $this->iblockId,
                'CODE'        => $code,
                'ACTIVE'      => 'Y',
                'CHECK_DATES' => 'Y',
            ],
            false,
            ['nTopCount' => 1],
            ['ID', 'NAME', 'CODE', 'ACTIVE_FROM', 'DETAIL_TEXT', 'DETAIL_TEXT_TYPE', 'PREVIEW_TEXT', 'PREVIEW_PICTURE', 'DETAIL_PICTURE', 'IBLOCK_SECTION_ID']
        );

        // GetNext() — аналогично findList(): возвращает NAME (экранировано) и ~NAME (сырое).
        return $rs->GetNext() ?: null;
    }

    /** Годы публикаций (по убыванию) для выпадающего фильтра. */
    public function getActiveYears(): array
    {
        $id = (int)$this->iblockId;
        $rs = Application::getConnection()->query("
            SELECT DISTINCT YEAR(ACTIVE_FROM) AS y
            FROM b_iblock_element
            WHERE IBLOCK_ID = {$id}
              AND ACTIVE = 'Y'
              AND ACTIVE_FROM IS NOT NULL
              AND ACTIVE_FROM <= NOW()
            ORDER BY y DESC
        ");

        $years = [];
        while ($row = $rs->fetch()) {
            if ($row['y'] !== null) {
                $years[] = (int)$row['y'];
            }
        }

        return $years;
    }
}
