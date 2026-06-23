<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class IblockTypeSeeder
{
    public function ensure(array $config): array
    {
        $obj = new CIBlockType();

        // Используем CIBlockType (а не D7 TypeTable::add) намеренно: он пишет
        // языковые названия в b_iblock_type_lang. Без этой записи тип не попадает
        // в CIBlockParameters::GetIBlockTypes() — выпадашка «Тип инфоблока» в
        // параметрах компонента его не показывает, и IBLOCK_TYPE сбивается на
        // первый доступный тип при сохранении. Тип content приходит из дампа БД
        // без языковой записи, поэтому даже для существующего типа досинхронизируем
        // названия через Update.
        if (CIBlockType::GetByID($config['ID'])->Fetch()) {
            if (!$obj->Update($config['ID'], ['LANG' => $config['LANG']])) {
                throw new RuntimeException(
                    'Не удалось обновить тип инфоблока: ' . $obj->LAST_ERROR
                );
            }
            return ["Тип инфоблока «{$config['ID']}» уже существует — синхронизированы названия"];
        }

        if (!$obj->Add($config)) {
            throw new RuntimeException(
                'Не удалось создать тип инфоблока: ' . $obj->LAST_ERROR
            );
        }

        return ["Создан тип инфоблока «{$config['ID']}»"];
    }
}
