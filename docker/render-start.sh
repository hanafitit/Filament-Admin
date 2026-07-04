#!/bin/sh
set -eu

APP_PORT="${PORT:-10000}"
SQLITE_PATH="${SQLITE_DB_DATABASE:-/var/data/database.sqlite}"

mkdir -p /app/storage/logs

if [ -z "${APP_DB_MODE:-}" ] && [ -n "${REMOTE_DB_HOST:-}" ]; then
    export APP_DB_MODE=remote
fi

if [ -n "$SQLITE_PATH" ]; then
    mkdir -p "$(dirname "$SQLITE_PATH")"
    touch "$SQLITE_PATH"
fi

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan migrate --force

if [ -n "${ADMIN_EMAIL:-}" ] && [ -n "${ADMIN_PASSWORD:-}" ]; then
    php artisan app:create-admin \
        --name="${ADMIN_NAME:-Admin}" \
        --email="$ADMIN_EMAIL" \
        --password="$ADMIN_PASSWORD" \
        --reset-password \
        --no-interaction
fi

php artisan queue:work --tries=1 --timeout=0 >> /app/storage/logs/queue.log 2>&1 &
php artisan schedule:work >> /app/storage/logs/scheduler.log 2>&1 &

exec php artisan serve --host=0.0.0.0 --port="$APP_PORT"
