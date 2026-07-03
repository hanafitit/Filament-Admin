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
    sqlite3 \
    && rm -rf /var/lib/apt/lists/*

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions \
    && install-php-extensions \
        bcmath \
        curl \
        gd \
        intl \
        mbstring \
        pcntl \
        pdo_pgsql \
        pdo_sqlite \
        zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN mkdir -p \
        storage/logs \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && composer dump-autoload --optimize --no-dev --no-interaction \
    && php artisan package:discover --ansi

COPY docker/render-start.sh /usr/local/bin/render-start
RUN chmod +x /usr/local/bin/render-start

EXPOSE 10000

CMD ["render-start"]
