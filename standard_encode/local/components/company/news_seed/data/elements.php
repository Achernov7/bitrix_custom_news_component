<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$faker = \Faker\Factory::create('ru_RU');
$faker->seed(42);

$sections = ['company', 'tech', 'finance', 'events', 'partners'];

$years = [2020, 2021, 2022, 2023, 2024];

// 30 уникальных картинок — каждый третий элемент получает свою (100 из 300 с изображением)
$images = array_map(fn($n) => "news-{$n}.jpg", range(1, 30));

$elements  = [];
$idx       = 0;
$imageIdx  = 0; // отдельный счётчик — не зависит от шага $idx

foreach ($years as $year) {
    for ($month = 1; $month <= 12; $month++) {
        foreach ($sections as $section) {
            $day = str_pad($faker->numberBetween(1, 28), 2, '0', STR_PAD_LEFT);
            $mm  = str_pad($month, 2, '0', STR_PAD_LEFT);

            $element = [
                'name'    => $faker->sentence(mt_rand(4, 9), false),
                'sec'     => $section,
                'date'    => "{$day}.{$mm}.{$year}",
                'preview' => $faker->paragraph(2),
                'detail'  => implode("\n\n", $faker->paragraphs(3)),
            ];

            if ($idx % 3 === 0) {
                $element['image'] = $images[$imageIdx % count($images)];
                $imageIdx++;
            }

            $elements[] = $element;
            $idx++;
        }
    }
}

return $elements;
