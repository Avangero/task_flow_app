#!/usr/bin/env bash
trap exit SIGINT

./vendor/bin/sail artisan test
./vendor/bin/duster fix
