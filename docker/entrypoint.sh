#!/bin/sh
set -e

WORKDIR=/var/www/html

if [ "${APP_ENV:-dev}" = "production" ]; then
    COMPOSER_FLAGS="--no-dev"
else
    COMPOSER_FLAGS=""
fi

if [ -f "$WORKDIR/composer.json" ]; then
    composer install \
        --working-dir="$WORKDIR" \
        --no-interaction \
        --no-progress \
        --prefer-dist \
        $COMPOSER_FLAGS
fi

if [ -f "/opt/php-tools/composer.json" ]; then
    composer install \
        --working-dir="/opt/php-tools" \
        --no-interaction \
        --no-progress \
        --prefer-dist \
        $COMPOSER_FLAGS
fi

DBCONN="$WORKDIR/bitrix/php_interface/dbconn.php"
SEED_SCRIPT="$WORKDIR/local/components/company/news_seed/bin/seed.php"

if [ "${APP_ENV:-dev}" != "production" ] && [ -f "$DBCONN" ] && [ -f "$SEED_SCRIPT" ]; then
    echo "[seed] Waiting for MySQL..."
    until php -r "new PDO('mysql:host=db;dbname=bitrix', 'bitrix', 'bitrix');" 2>/dev/null; do
        sleep 2
    done
    echo "[seed] Running news seeder..."
    php "$SEED_SCRIPT"
fi

exec docker-php-entrypoint "$@"
