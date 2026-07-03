#!/bin/sh
set -eu

APP_PORT="${PORT:-10000}"
SQLITE_PATH="${SQLITE_DB_DATABASE:-/var/data/database.sqlite}"

mkdir -p /app/storage/logs

if [ -n "$SQLITE_PATH" ]; then
    mkdir -p "$(dirname "$SQLITE_PATH")"
    touch "$SQLITE_PATH"
fi

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan migrate --force

php artisan queue:work --tries=1 --timeout=0 >> /app/storage/logs/queue.log 2>&1 &
php artisan schedule:work >> /app/storage/logs/scheduler.log 2>&1 &

exec php artisan serve --host=0.0.0.0 --port="$APP_PORT"
