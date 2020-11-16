#!/bin/bash

#On error no such file entrypoint.sh, execute in terminal - dos2unix .docker\entrypoint.sh
cp .env.testing.example .env.testing
cp .env.example .env
chown -R www-data:www-data .
composer install
php artisan key:generate
php artisan migrate --seed

php-fpm
