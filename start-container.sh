#!/bin/sh
set -eu

cd /app

# This path must be a Railway volume, not the disposable application image.
: "${EVENTKU_DATA_PATH:?Set EVENTKU_DATA_PATH to the persistent volume mount}"
: "${APP_KEY:?Provide a stable APP_KEY in Railway variables}"
if [ ! -d "$EVENTKU_DATA_PATH" ]; then
    echo 'Persistent data volume is missing; refusing to start.' >&2
    exit 1
fi

export LARAVEL_STORAGE_PATH="$EVENTKU_DATA_PATH/storage"
mkdir -p "$LARAVEL_STORAGE_PATH/app/public/posters" \
    "$LARAVEL_STORAGE_PATH/framework/cache/data" \
    "$LARAVEL_STORAGE_PATH/framework/sessions" \
    "$LARAVEL_STORAGE_PATH/framework/views" \
    "$LARAVEL_STORAGE_PATH/framework/testing" \
    "$LARAVEL_STORAGE_PATH/logs" bootstrap/cache

if [ "${DB_CONNECTION:-}" = sqlite ]; then
    export DB_DATABASE="$EVENTKU_DATA_PATH/database.sqlite"
    # Never truncate an existing database on restart.
    if [ ! -f "$DB_DATABASE" ]; then
        touch "$DB_DATABASE"
    fi
fi

# Bundle only the approved demo posters, never local uploads or payment proof.
if [ -d resources/demo/posters ]; then
    for poster in resources/demo/posters/*; do
        [ -f "$poster" ] || continue
        destination="$LARAVEL_STORAGE_PATH/app/public/posters/$(basename "$poster")"
        [ -e "$destination" ] || cp "$poster" "$destination"
    done
fi

# Railpack may build caches before a volume is mounted; resolve runtime env now.
php artisan config:clear --no-interaction
php artisan package:discover --no-interaction
php artisan migrate --force --no-interaction
php artisan eventku:seed-demo --no-interaction
php artisan storage:link --force --no-interaction
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction

# One replica only: web and lightweight payment reconciliation share the volume.
php artisan schedule:work --no-interaction &
scheduler_pid=$!
frankenphp run --config /Caddyfile --adapter caddyfile &
web_pid=$!

stop_children() {
    kill -TERM "$web_pid" "$scheduler_pid" 2>/dev/null || true
    wait "$web_pid" "$scheduler_pid" 2>/dev/null || true
}
trap 'stop_children; exit 0' TERM INT
trap stop_children EXIT

# A failed scheduler must not silently disable payment reconciliation.
while kill -0 "$web_pid" 2>/dev/null && kill -0 "$scheduler_pid" 2>/dev/null; do
    sleep 5 &
    wait $! || true
done
echo 'A runtime process exited; restarting the container.' >&2
exit 1
