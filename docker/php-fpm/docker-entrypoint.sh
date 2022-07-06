#!/bin/sh
set -e

cd /app

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

mkdir -p var/cache var/log
composer install --prefer-dist --no-progress --no-interaction

bin/console cache:clear
bin/console cache:warmup

# start php-fpm
php-fpm
