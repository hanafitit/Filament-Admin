#!/bin/sh
set -eu

APP_PORT="${PORT:-10000}"
SQLITE_PATH="${SQLITE_DB_DATABASE:-/var/data/database.sqlite}"

mkdir -p /app/storage/logs

log() {
    echo "[render-start] $1"
}

if [ -n "$SQLITE_PATH" ]; then
    mkdir -p "$(dirname "$SQLITE_PATH")"
    touch "$SQLITE_PATH"
fi

log "Clearing Laravel caches"
php artisan config:clear
php artisan route:clear
php artisan view:clear

log "Running local migrations"
php artisan migrate --force

if [ -n "${REMOTE_DB_HOST:-}" ] && [ -n "${REMOTE_DB_DATABASE:-}" ] && [ -n "${REMOTE_DB_USERNAME:-}" ]; then
    log "Pulling remote database into local SQLite"
    if ! php artisan app:pull-remote-database; then
        log "Remote database pull failed, continuing startup with local SQLite state"
    fi
else
    log "Remote database connection variables are incomplete, skipping remote pull"
fi

if [ -n "${ADMIN_EMAIL:-}" ] && [ -n "${ADMIN_PASSWORD:-}" ]; then
    log "Ensuring admin user exists in local database"
    if ! php artisan app:create-admin \
        --name="${ADMIN_NAME:-Admin}" \
        --email="$ADMIN_EMAIL" \
        --password="$ADMIN_PASSWORD" \
        --reset-password \
        --no-interaction; then
        log "Admin creation failed, continuing startup"
    fi
else
    log "Admin credentials are not fully set, skipping admin creation"
fi

log "Starting queue worker"
php artisan queue:work --tries=1 --timeout=0 >> /app/storage/logs/queue.log 2>&1 &

log "Starting scheduler"
php artisan schedule:work >> /app/storage/logs/scheduler.log 2>&1 &

log "Starting HTTP server on port ${APP_PORT}"
exec php artisan serve --host=0.0.0.0 --port="$APP_PORT"
