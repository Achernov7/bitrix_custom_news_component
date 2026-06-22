<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Iblock\ElementTable;

class ElementSeeder
{
    private string $imagesDir;

    public function __construct()
    {
        $this->imagesDir = __DIR__ . '/../data/images/';
    }

    public function seed(int $iblockId, array $sectionIds, array $elements): array
    {
        $obj = new CIBlockElement();

        $allCodes = [];
        foreach ($elements as $i => $item) {
            $allCodes[] = 'news-' . ($i + 1);
        }

        // D7 гарантирует корректный IN-фильтр по массиву кодов;
        // старый CIBlockElement::GetList с ['CODE' => $array] не всегда работает как IN.
        $existing = [];
        $rs = ElementTable::getList([
            'filter' => ['=IBLOCK_ID' => $iblockId, '=CODE' => $allCodes],
            'select' => ['CODE'],
        ]);
        while ($row = $rs->fetch()) {
            $existing[$row['CODE']] = true;
        }

        $toInsert = array_filter(
            $elements,
            fn($i) => !isset($existing['news-' . ($i + 1)]),
            ARRAY_FILTER_USE_KEY
        );

        foreach ($toInsert as $i => $item) {
            $code = 'news-' . ($i + 1);

            $fields = [
                'IBLOCK_ID'         => $iblockId,
                'IBLOCK_SECTION_ID' => $sectionIds[$item['sec']],
                'NAME'              => $item['name'],
                'CODE'              => $code,
                'ACTIVE'            => 'Y',
                'ACTIVE_FROM'       => $item['date'],
                'PREVIEW_TEXT'      => $item['preview'] ?? $item['name'],
                'PREVIEW_TEXT_TYPE' => 'text',
                'DETAIL_TEXT'       => $item['detail'] ?? $item['preview'] ?? $item['name'],
                'DETAIL_TEXT_TYPE'  => 'text',
                'SORT'              => ($i + 1) * 10,
            ];

            if (!empty($item['image'])) {
                $path = $this->imagesDir . $item['image'];
                if (file_exists($path)) {
                    $file = CFile::MakeFileArray($path);
                    if ($file) {
                        $fields['PREVIEW_PICTURE'] = $file;
                        $fields['DETAIL_PICTURE']  = CFile::MakeFileArray($path);
                    }
                }
            }

            $id = (int)$obj->Add($fields);

            if (!$id) {
                throw new RuntimeException("Ошибка создания «{$item['name']}»: " . $obj->LAST_ERROR);
            }
        }

        $created = count($toInsert);
        $skipped = count($elements) - $created;

        return ["Элементы — создано: {$created}, пропущено (уже есть): {$skipped}"];
    }
}
