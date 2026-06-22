<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Iblock\IblockTable;

class IblockSeeder
{
    private int $iblockId = 0;

    public function ensure(array $config): array
    {
        $existing = IblockTable::getList([
            'filter' => ['=IBLOCK_TYPE_ID' => $config['IBLOCK_TYPE_ID'], '=CODE' => $config['CODE'], '=ACTIVE' => 'Y'],
            'select' => ['ID'],
            'limit'  => 1,
        ])->fetch();

        if ($existing) {
            $this->iblockId = (int)$existing['ID'];
            return ["Инфоблок «{$config['CODE']}» уже существует (ID={$this->iblockId})"];
        }

        $ib = new CIBlock();
        $id = $ib->Add($config);

        if (!$id) {
            throw new RuntimeException(
                'Не удалось создать инфоблок: ' . $ib->LAST_ERROR
            );
        }

        $this->iblockId = $id;
        return ["Создан инфоблок «{$config['CODE']}» (ID={$this->iblockId})"];
    }

    public function getIblockId(): int
    {
        return $this->iblockId;
    }
}
