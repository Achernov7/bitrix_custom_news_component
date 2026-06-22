FROM php:8.2-fpm-bookworm

# System deps for PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libicu-dev \
    libzip-dev \
    libxml2-dev \
    libxslt1-dev \
    libcurl4-openssl-dev \
    libonig-dev \
    libwebp-dev \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# GD with freetype + jpeg + webp
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp

# All extensions Bitrix needs
RUN docker-php-ext-install -j$(nproc) \
    mysqli \
    pdo_mysql \
    gd \
    mbstring \
    curl \
    xml \
    intl \
    zip \
    opcache \
    bcmath \
    sockets \
    xsl \
    pcntl \
    exif

# Redis client extension (phpredis) for Bitrix cache engine
RUN pecl install redis \
    && docker-php-ext-enable redis

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html


COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["sh", "/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
