<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

class NewsSeeder
{
    public function __construct(
        private readonly string $dataDir,
        private readonly IblockTypeSeeder $iblockTypeSeeder,
        private readonly IblockSeeder $iblockSeeder,
        private readonly SectionSeeder $sectionSeeder,
        private readonly ElementSeeder $elementSeeder,
    ) {}

    public function run(): array
    {
        if (!Loader::includeModule('iblock')) {
            throw new RuntimeException('Модуль iblock не установлен');
        }

        global $USER;
        $USER->Authorize(1);

        $dataDir = rtrim($this->dataDir, '/') . '/';

        $log = array_merge(
            $this->iblockTypeSeeder->ensure(require $dataDir . 'iblock_type.php'),
            $this->iblockSeeder->ensure(require $dataDir . 'iblock.php'),
        );

        $iblockId = $this->iblockSeeder->getIblockId();

        return array_merge(
            $log,
            $this->sectionSeeder->ensure($iblockId, require $dataDir . 'sections.php'),
            $this->elementSeeder->seed(
                $iblockId,
                $this->sectionSeeder->getSectionIds(),
                require $dataDir . 'elements.php',
            ),
        );
    }
}
