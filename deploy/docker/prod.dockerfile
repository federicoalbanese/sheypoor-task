FROM php:8.4-fpm-alpine

RUN apk update  \
    && apk add zip zlib-dev libzip-dev icu-dev libxml2-dev \
    && apk add --no-cache pcre-dev $PHPIZE_DEPS \
    && apk add --update linux-headers \
    && apk add --no-cache git

RUN pecl install redis && docker-php-ext-enable redis
RUN docker-php-ext-configure zip
RUN docker-php-ext-install -j$(nproc) pdo pdo_mysql intl soap pcntl zip opcache

ARG UID
ARG GID
ENV UID=${UID}
ENV GID=${GID}
ENV PHPGROUP=laravel
ENV PHPUSER=laravel

RUN addgroup -g ${GID} ${PHPGROUP}
RUN adduser -G ${PHPGROUP} -D -s /bin/sh -u ${UID} ${PHPUSER}

RUN sed -i "s/user = www-data/user = ${PHPUSER}/g" /usr/local/etc/php-fpm.d/www.conf
RUN sed -i "s/group = www-data/group = ${PHPGROUP}/g" /usr/local/etc/php-fpm.d/www.conf

RUN mkdir -p /var/www/html/public

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

USER ${PHPUSER}

CMD ["php-fpm", "--nodaemonize"]
