<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */

/** @var CBitrixComponentTemplate $this */

$this->addExternalCss($this->GetFolder() . '/css/news.css');

$listUrl = htmlspecialchars($arResult['LIST_URL']); // на section-странице — SEF-URL категории
$folder  = htmlspecialchars($arResult['FOLDER']);   // база раздела: «все новости»
$section = $arResult['SECTION'] ?? null;            // текущая категория (SEF) или null
?>
<div class="news-list">

    <?php if ($section): ?>
        <h1 class="news-list__heading">Категория: <?= htmlspecialchars($section['NAME']) ?></h1>
    <?php endif; ?>

    <form class="news-filter" method="get" action="<?= $listUrl ?>">
        <div class="news-filter__row">
            <select name="year" class="news-filter__select">
                <option value="">Все годы</option>
                <?php foreach ($arResult['YEARS'] as $year): ?>
                    <option value="<?= (int)$year ?>"<?= $arResult['FILTER']['year'] == $year ? ' selected' : '' ?>>
                        <?= (int)$year ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if (!$section): /* на section-странице категория задана URL-путём */ ?>
            <select name="category" class="news-filter__select">
                <option value="">Все категории</option>
                <?php foreach ($arResult['SECTIONS'] as $sec): ?>
                    <option value="<?= (int)$sec['ID'] ?>"<?= $arResult['FILTER']['category'] == $sec['ID'] ? ' selected' : '' ?>>
                        <?= htmlspecialchars($sec['NAME']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>

            <label class="news-filter__label">
                с&nbsp;<input class="news-filter__date" type="text" name="date_from"
                    value="<?= htmlspecialchars($arResult['FILTER']['date_from']) ?>"
                    placeholder="ДД.ММ.ГГГГ" maxlength="10">
            </label>
            <label class="news-filter__label">
                по&nbsp;<input class="news-filter__date" type="text" name="date_to"
                    value="<?= htmlspecialchars($arResult['FILTER']['date_to']) ?>"
                    placeholder="ДД.ММ.ГГГГ" maxlength="10">
            </label>

            <button type="submit" class="news-filter__btn">Применить</button>
            <a href="<?= $folder ?>" class="news-filter__reset">Сбросить</a>
        </div>
    </form>

    <?php if ($arResult['FILTER_ERROR']): ?>
        <div class="news-filter-error">
            <?= htmlspecialchars($arResult['FILTER_ERROR']) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($arResult['ITEMS'])): ?>
        <p class="news-list__empty">Новостей не найдено.</p>
    <?php else: ?>

        <div class="news-items">
            <?php foreach ($arResult['ITEMS'] as $arItem): ?>
                <article class="news-item">
                    <?php if ($arItem['PREVIEW_PICTURE']): ?>
                        <?php $img = CFile::ResizeImageGet($arItem['PREVIEW_PICTURE'], ['width' => 280, 'height' => 180], BX_RESIZE_IMAGE_PROPORTIONAL, false); ?>
                        <?php if (!empty($img['src'])): ?>
                        <a href="<?= htmlspecialchars($arItem['DETAIL_URL']) ?>" class="news-item__img-link">
                            <img class="news-item__img" src="<?= htmlspecialchars($img['src']) ?>"
                                 alt="<?= $arItem['NAME'] ?>" loading="lazy">
                        </a>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="news-item__body">
                        <div class="news-item__meta">
                            <?php if ($arItem['DATE_ACTIVE_FROM']): ?>
                                <time class="news-item__date"><?= htmlspecialchars($arItem['DATE_ACTIVE_FROM']) ?></time>
                            <?php endif; ?>
                            <?php if ($arItem['SECTION']): ?>
                                <a class="news-item__category" href="<?= htmlspecialchars($arItem['SECTION']['URL']) ?>"><?= htmlspecialchars($arItem['SECTION']['NAME']) ?></a>
                            <?php endif; ?>
                        </div>

                        <h2 class="news-item__title">
                            <a href="<?= htmlspecialchars($arItem['DETAIL_URL']) ?>">
                                <?= $arItem['NAME'] ?>
                            </a>
                        </h2>

                        <?php if ($arItem['PREVIEW_TEXT_SHORT']): ?>
                            <p class="news-item__preview"><?= htmlspecialchars($arItem['PREVIEW_TEXT_SHORT']) ?></p>
                        <?php endif; ?>

                        <a href="<?= htmlspecialchars($arItem['DETAIL_URL']) ?>" class="news-item__more">
                            Читать далее &rarr;
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($arResult['NAV_STRING'])): ?>
            <div class="news-pager"><?= $arResult['NAV_STRING'] ?></div>
        <?php endif; ?>

    <?php endif; ?>

</div>
