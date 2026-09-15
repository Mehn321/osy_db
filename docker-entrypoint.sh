#!/bin/bash
set -e

echo "Application runtime: PHP $(php -r 'echo PHP_VERSION;') | OpenSSL $(php -r 'echo defined("OPENSSL_VERSION_TEXT") ? OPENSSL_VERSION_TEXT : "unavailable";') | Hostname $(hostname)"

# Make sure uploads directories exist and are writable
mkdir -p /var/www/html/uploads/govt_ids
mkdir -p /var/www/html/uploads/profiles
mkdir -p /var/www/html/uploads/providers
chown -R www-data:www-data /var/www/html/uploads

# Execute the CMD from the Dockerfile
exec "$@"
