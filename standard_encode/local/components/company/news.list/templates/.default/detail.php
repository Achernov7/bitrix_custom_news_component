<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */

/** @var CBitrixComponentTemplate $this */

$this->addExternalCss($this->GetFolder() . '/css/detail.css');

$arEl = $arResult['ELEMENT'];
$backUrl = htmlspecialchars($arResult['FOLDER']);

// Resolve picture: prefer DETAIL_PICTURE, fallback to PREVIEW_PICTURE
$pictureId = $arEl['DETAIL_PICTURE'] ?: ($arEl['PREVIEW_PICTURE'] ?: null);
$img = null;
if ($pictureId) {
    $img = CFile::ResizeImageGet($pictureId, ['width' => 800, 'height' => 500], BX_RESIZE_IMAGE_PROPORTIONAL, false);
}
?>
<article class="news-detail">

    <div class="news-detail__meta">
        <?php if ($arEl['DATE_ACTIVE_FROM']): ?>
            <time class="news-detail__date"><?= htmlspecialchars($arEl['DATE_ACTIVE_FROM']) ?></time>
        <?php endif; ?>
        <?php if ($arEl['SECTION']): ?>
            <a class="news-detail__category" href="<?= htmlspecialchars($arEl['SECTION']['URL']) ?>"><?= htmlspecialchars($arEl['SECTION']['NAME']) ?></a>
        <?php endif; ?>
    </div>

    <h1 class="news-detail__title"><?= $arEl['NAME'] ?></h1>

    <?php if ($img && !empty($img['src'])): ?>
        <div class="news-detail__img-wrap">
            <img class="news-detail__img"
                 src="<?= htmlspecialchars($img['src']) ?>"
                 alt="<?= $arEl['NAME'] ?>">
        </div>
    <?php endif; ?>

    <div class="news-detail__text">
        <?php
        $detailText = trim((string)($arEl['~DETAIL_TEXT'] ?? ''));
        $previewText = trim((string)($arEl['~PREVIEW_TEXT'] ?? ''));
        $text = $detailText ?: $previewText;

        if ($arEl['DETAIL_TEXT_TYPE'] === 'text') {
            echo nl2br(htmlspecialchars($text));
        } else {
            echo $text;
        }
        ?>
    </div>

    <div class="news-detail__back">
        <a href="<?= $backUrl ?>">&larr; Все новости</a>
    </div>

</article>
