FROM nginx:1.21.6-alpine

WORKDIR /app

RUN apk --update upgrade

COPY ./docker/web/config/nginx.conf /etc/nginx/conf.d/default.conf
COPY ./public ./public
