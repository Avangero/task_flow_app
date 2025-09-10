#!/usr/bin/env bash
trap exit SIGINT

cp .env.example .env
composer install
php artisan key:generate
./vendor/bin/sail up -d
sleep 10
./vendor/bin/sail artisan migrate:fresh --seed
./vendor/bin/sail artisan jwt:secret
./vendor/bin/sail artisan optimize
