<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Iblock\SectionTable;

class SectionSeeder
{
    private array $sectionIds = [];

    public function ensure(int $iblockId, array $sections): array
    {
        $allCodes = array_column($sections, 'CODE');

        $existing = [];
        $db = SectionTable::getList([
            'filter' => ['=IBLOCK_ID' => $iblockId, '=CODE' => $allCodes],
            'select' => ['ID', 'CODE'],
        ]);
        while ($row = $db->fetch()) {
            $existing[$row['CODE']] = (int)$row['ID'];
        }

        foreach ($existing as $code => $id) {
            $this->sectionIds[$code] = $id;
        }

        $toInsert = array_filter($sections, fn($s) => !isset($existing[$s['CODE']]));

        foreach ($toInsert as $sec) {
            $result = SectionTable::add([
                'IBLOCK_ID' => $iblockId,
                'ACTIVE'    => 'Y',
                'NAME'      => $sec['NAME'],
                'CODE'      => $sec['CODE'],
            ]);

            if (!$result->isSuccess()) {
                throw new RuntimeException(
                    "Не удалось создать раздел «{$sec['NAME']}»: " . implode(', ', $result->getErrorMessages())
                );
            }

            $this->sectionIds[$sec['CODE']] = $result->getId();
        }

        $created = count($toInsert);
        $skipped = count($sections) - $created;

        return ["Разделы — создано: {$created}, пропущено (уже есть): {$skipped}"];
    }

    public function getSectionIds(): array
    {
        return $this->sectionIds;
    }
}
