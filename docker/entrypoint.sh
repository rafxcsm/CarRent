#!/bin/sh
set -eu

if [ -z "${APP_URL:-}" ] && [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
    export APP_URL="$RENDER_EXTERNAL_URL"
fi

php artisan config:clear
php artisan config:cache
php artisan migrate --force
php artisan view:cache

exec "$@"
