<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Iblock\TypeTable;

class IblockTypeSeeder
{
    public function ensure(array $config): array
    {
        $existing = TypeTable::getList([
            'filter' => ['=ID' => $config['ID']],
            'limit'  => 1,
        ])->fetch();

        if ($existing) {
            return ["Тип инфоблока «{$config['ID']}» уже существует"];
        }

        $result = TypeTable::add($config);
        if (!$result->isSuccess()) {
            throw new RuntimeException(
                'Не удалось создать тип инфоблока: ' . implode(', ', $result->getErrorMessages())
            );
        }

        return ["Создан тип инфоблока «{$config['ID']}»"];
    }
}
