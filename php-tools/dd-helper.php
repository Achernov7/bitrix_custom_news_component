<?php
/**
 * dd() / dump() helper — controlled by APP_DEBUG env var.
 *
 * APP_DEBUG=1  → full dump + die (dd) / dump without stopping (dump)
 * APP_DEBUG=0  → silent no-op
 *
 * Auto-prepended to every PHP request via php.ini auto_prepend_file.
 */

$isDebug = filter_var(getenv('APP_DEBUG') ?: '0', FILTER_VALIDATE_BOOL);

if (!function_exists('dd')) {
    function dd(mixed ...$vars): never
    {
        global $isDebug;
        if ($isDebug) {
            if (!in_array(PHP_SAPI, ['cli', 'phpdbg', 'embed'], true) && !headers_sent()) {
                header('HTTP/1.1 500 Internal Server Error');
            }
            foreach ($vars ?: ['🐛'] as $v) {
                if (class_exists(\Symfony\Component\VarDumper\VarDumper::class)) {
                    \Symfony\Component\VarDumper\VarDumper::dump($v);
                } else {
                    var_dump($v);
                }
            }
        }
        exit(1);
    }
}

if (!function_exists('dump')) {
    function dump(mixed ...$vars): mixed
    {
        global $isDebug;
        if (!$isDebug) {
            return $vars[0] ?? null;
        }
        foreach ($vars ?: ['🐛'] as $v) {
            if (class_exists(\Symfony\Component\VarDumper\VarDumper::class)) {
                \Symfony\Component\VarDumper\VarDumper::dump($v);
            } else {
                var_dump($v);
            }
        }
        return count($vars) === 1 ? $vars[0] : $vars;
    }
}

// symfony/var-dumper — require-dev в php-tools/composer.json, устанавливается при APP_ENV != production.
$globalAutoload = '/opt/php-tools/vendor/autoload.php';
if (file_exists($globalAutoload)) {
    require_once $globalAutoload;
}
