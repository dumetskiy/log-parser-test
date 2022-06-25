FROM php:8.1.1-fpm-alpine

ARG USER=www-data
ARG UID=1000

RUN if [ $USER != "www-data" ]; then \
    addgroup -g ${UID} -S ${USER} \
    && adduser -D -H -u ${UID} -s /bin/bash ${USER} -G ${USER}; \
fi;

WORKDIR /app
USER root

RUN apk update \
    && apk add  --no-cache git curl \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /tmp/* \
    && docker-php-source extract

COPY --chown=${USER}:${USER} . .
USER ${USER}

CMD ["php-fpm", "-F"]

EXPOSE 9000