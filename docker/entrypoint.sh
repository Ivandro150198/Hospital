#!/bin/sh
set -eu

PORT="${PORT:-10000}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

ADMIN_USER="${ADMIN_USERNAME:-admin}"
ADMIN_HASH="${ADMIN_PASSWORD_HASH:-}"
php -r '
$user = getenv("ADMIN_USERNAME") ?: "admin";
$hash = getenv("ADMIN_PASSWORD_HASH") ?: "";
$file = "/var/www/html/config/auth.php";
$content = "<?php\nreturn [\n    \"username\" => " . var_export($user, true) . ",\n    \"password_hash\" => " . var_export($hash, true) . ",\n];\n";
file_put_contents($file, $content);
chmod($file, 0640);
'
chown www-data:www-data /var/www/html/config/auth.php

if [ -z "$ADMIN_HASH" ]; then
    echo "HMH: ADMIN_PASSWORD_HASH em falta. O painel admin nao aceita login ate definir esta variavel." >&2
fi

DATA_ROOT="${HMH_DATA_ROOT:-}"
if [ -n "$DATA_ROOT" ]; then
    mkdir -p "$DATA_ROOT/data" "$DATA_ROOT/images" "$DATA_ROOT/mensagens"
    if [ -d /opt/hmh-seed/data ]; then
        for f in /opt/hmh-seed/data/*; do
            [ -f "$f" ] || continue
            base=$(basename "$f")
            if [ ! -f "$DATA_ROOT/data/$base" ]; then
                cp "$f" "$DATA_ROOT/data/$base"
            fi
        done
    fi
    if [ -d /opt/hmh-seed/images ]; then
        for f in /opt/hmh-seed/images/*; do
            [ -f "$f" ] || continue
            base=$(basename "$f")
            if [ ! -f "$DATA_ROOT/images/$base" ]; then
                cp "$f" "$DATA_ROOT/images/$base"
            fi
        done
    fi
    rm -rf /var/www/html/data /var/www/html/images /var/www/html/mensagens
    ln -s "$DATA_ROOT/data" /var/www/html/data
    ln -s "$DATA_ROOT/images" /var/www/html/images
    ln -s "$DATA_ROOT/mensagens" /var/www/html/mensagens
    chown -R www-data:www-data "$DATA_ROOT"
fi

exec apache2-foreground
