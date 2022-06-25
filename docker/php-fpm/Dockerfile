FROM php:8.1.1-fpm-alpine

RUN apk update \
    && apk add  --no-cache git curl \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /tmp/* \
    && docker-php-source extract

CMD ["php-fpm", "-F"]

WORKDIR /data/app

EXPOSE 9000