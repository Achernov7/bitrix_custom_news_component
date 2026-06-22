<?php

namespace Company\NewsList\Service;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use CPHPCache;

/**
 * Тонкая обёртка над CPHPCache в стиле remember(): вычисляет данные через
 * колбэк только при промахе кеша. Если колбэк вернул null — кеш не пишется.
 */
class NewsCache
{
    public function __construct(private readonly int $ttl) {}

    public function remember(string $id, string $dir, callable $producer): mixed
    {
        $cache = new CPHPCache();

        if ($cache->StartDataCache($this->ttl, $id, $dir)) {
            $data = $producer();
            if ($data === null) {
                $cache->AbortDataCache();
                return null;
            }
            $cache->EndDataCache($data);
            return $data;
        }

        return $cache->GetVars();
    }
}
