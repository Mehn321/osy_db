#!/bin/bash
set -e

# Make sure uploads directories exist and are writable
mkdir -p /var/www/html/uploads/govt_ids
mkdir -p /var/www/html/uploads/profiles
mkdir -p /var/www/html/uploads/providers
chown -R www-data:www-data /var/www/html/uploads

# Execute the CMD from the Dockerfile
exec "$@"
