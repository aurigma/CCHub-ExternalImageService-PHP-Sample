#!/bin/bash

echo "⏳ Waiting for MySQL at $DB_HOST..."
until mysql -h"$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "SELECT 1" &>/dev/null; do
  sleep 10
  echo "⏳ Waiting for MySQL..."
done

echo "✅ MySQL is available!"

echo "⏳ Checking if the database exists..."

mysql -h"$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS $DB_DATABASE;"

echo "🚀 Make migrations..."
php artisan migrate --force

echo "🔑 Generating keys..."
php artisan key:generate
php artisan jwt:secret

php artisan serve --host=0.0.0.0 --port=8000