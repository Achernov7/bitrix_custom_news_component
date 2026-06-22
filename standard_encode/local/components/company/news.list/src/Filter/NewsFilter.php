<?php

namespace Company\NewsList\Filter;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * Фильтр списка новостей: чтение, санитизация и валидация GET-параметров.
 *
 * Год / категория / диапазон дат комбинируются по AND. Явный диапазон дат
 * имеет приоритет над годом. При некорректном диапазоне выставляется ошибка,
 * а условия фильтра не применяются (список не ломается).
 */
class NewsFilter
{
    private array $raw;
    private string $error = '';
    private int|false $tsFrom = false;
    private int|false $tsTo = false;

    public function __construct(array $get)
    {
        $this->raw = [
            'year'      => preg_replace('/\D/', '', (string)($get['year'] ?? '')),
            'category'  => (int)($get['category'] ?? 0),
            'date_from' => preg_replace('/[^\d.]/', '', (string)($get['date_from'] ?? '')),
            'date_to'   => preg_replace('/[^\d.]/', '', (string)($get['date_to'] ?? '')),
        ];
        $this->parseDates();
    }

    /**
     * Жёстко задаёт категорию из SEF-пути (/news/category/CODE/).
     */
    public function forceCategory(int $sectionId): void
    {
        $this->raw['category'] = $sectionId;
    }

    public function hasError(): bool
    {
        return $this->error !== '';
    }

    public function getError(): string
    {
        return $this->error;
    }

    /** Очищенные значения фильтра — для повторного вывода в форме. */
    public function getRaw(): array
    {
        return $this->raw;
    }

    /** Условия фильтра для подмешивания в фильтр инфоблока. */
    public function toConditions(): array
    {
        if ($this->hasError()) {
            return [];
        }

        $conditions = [];

        if ($this->tsFrom !== false || $this->tsTo !== false) {
            // Явный диапазон дат имеет приоритет над годом.
            // DATE_ACTIVE_FROM роутится через FilterCreateEx и поддерживает все операторы,
            // в отличие от ACTIVE_FROM (case в GetList генерирует только >= или <).
            if ($this->tsFrom !== false) {
                $conditions['>=DATE_ACTIVE_FROM'] = ConvertTimeStamp($this->tsFrom, 'FULL');
            }
            if ($this->tsTo !== false) {
                $conditions['<=DATE_ACTIVE_FROM'] = ConvertTimeStamp($this->tsTo, 'FULL');
            }
        } elseif ($this->raw['year'] !== '') {
            $year = (int)$this->raw['year'];
            $conditions['>=DATE_ACTIVE_FROM'] = ConvertTimeStamp(mktime(0, 0, 0, 1, 1, $year), 'FULL');
            $conditions['<=DATE_ACTIVE_FROM'] = ConvertTimeStamp(mktime(23, 59, 59, 12, 31, $year), 'FULL');
        }

        if ($this->raw['category'] > 0) {
            $conditions['SECTION_ID'] = $this->raw['category'];
        }

        return $conditions;
    }

    private function parseDates(): void
    {
        if ($this->raw['date_from'] !== '') {
            $this->tsFrom = $this->toTimestamp($this->raw['date_from'], false);
            if ($this->tsFrom === false) {
                $this->error = 'Некорректная дата «с» (формат: ДД.ММ.ГГГГ)';
                return;
            }
        }

        if ($this->raw['date_to'] !== '') {
            $this->tsTo = $this->toTimestamp($this->raw['date_to'], true);
            if ($this->tsTo === false) {
                $this->error = 'Некорректная дата «по» (формат: ДД.ММ.ГГГГ)';
                return;
            }
        }

        if ($this->tsFrom !== false && $this->tsTo !== false && $this->tsFrom > $this->tsTo) {
            $this->error = 'Дата «с» не может быть позже даты «по»';
            $this->tsFrom = $this->tsTo = false;
        }
    }

    /** Разбирает дату «ДД.ММ.ГГГГ» в timestamp; false — если дата некорректна. */
    private function toTimestamp(string $value, bool $endOfDay): int|false
    {
        $p = explode('.', $value);
        if (count($p) !== 3 || !checkdate((int)$p[1], (int)$p[0], (int)$p[2])) {
            return false;
        }

        return $endOfDay
            ? mktime(23, 59, 59, (int)$p[1], (int)$p[0], (int)$p[2])
            : mktime(0, 0, 0, (int)$p[1], (int)$p[0], (int)$p[2]);
    }
}
