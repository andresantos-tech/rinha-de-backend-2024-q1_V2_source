FROM ghcr.io/roadrunner-server/roadrunner:2023.3 AS roadrunner
FROM composer:2 AS composer
FROM mlocati/php-extension-installer as php-installer
FROM php:8.3-cli

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr
COPY --from=php-installer /usr/bin/install-php-extensions /usr/local/bin/install-php-extensions

RUN apt-get update \
    && apt-get -y --no-install-recommends install zip unzip \
    && mkdir /.composer && chmod 777 /.composer

RUN install-php-extensions \
    gd \
    zip \
    opcache \
    calendar \
    intl \
    exif \
    bcmath \
    opcache \
    pcntl \
    sockets \
    mbstring \
    curl \
    inotify \
    intl \
    pdo_pgsql \
    pdo_mysql \
    redis

COPY ./app /app
COPY ./app/entrypoint.sh /usr/bin/entrypoint.sh
RUN chmod a+x /usr/bin/entrypoint.sh

WORKDIR /app

EXPOSE 3000

CMD ["/usr/bin/entrypoint.sh"]

# docker build -t devnote/php-8.2-rr:rinha-backend-v1 .
# docker build -t devnote/php-8.3-rr:rinha-backend-v2 .