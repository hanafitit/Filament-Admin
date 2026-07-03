FROM node:22-bookworm-slim AS assets

WORKDIR /app

COPY package.json ./
RUN npm install

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./

RUN npm run build

FROM php:8.3-cli-bookworm

WORKDIR /app

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV PORT=10000

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libcurl4-openssl-dev \
    libfreetype6-dev \
    libicu-dev \
    libjpeg62-turbo-dev \
    libonig-dev \
    libpq-dev \
    libpng-dev \
    libsqlite3-dev \
    libxml2-dev \
    libzip-dev \
    sqlite3 \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        bcmath \
        curl \
        dom \
        gd \
        intl \
        mbstring \
        pcntl \
        pdo_pgsql \
        pdo_sqlite \
        simplexml \
        xml \
        xmlreader \
        xmlwriter \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN mkdir -p storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && composer dump-autoload --optimize --no-dev --no-interaction \
    && php artisan package:discover --ansi

COPY docker/render-start.sh /usr/local/bin/render-start
RUN chmod +x /usr/local/bin/render-start

EXPOSE 10000

CMD ["render-start"]
