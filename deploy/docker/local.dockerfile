FROM php:8.4-fpm-alpine

RUN apk update \
    && apk add zip zlib-dev libzip-dev icu-dev libxml2-dev \
    && apk add --no-cache pcre-dev $PHPIZE_DEPS \
    && apk add --update linux-headers \
    && apk add --no-cache git

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Xdebug extension
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Configure and install PHP extensions
RUN docker-php-ext-configure zip
RUN docker-php-ext-install -j$(nproc) pdo pdo_mysql intl soap pcntl zip sockets

ARG UID
ARG GID
ENV UID=${UID}
ENV GID=${GID}
ENV PHPGROUP=laravel
ENV PHPUSER=laravel

# Create user and group
RUN addgroup -g ${GID} ${PHPGROUP}
RUN adduser -G ${PHPGROUP} -D -s /bin/sh -u ${UID} ${PHPUSER}

# Configure PHP-FPM user and group
RUN sed -i "s/user = www-data/user = ${PHPUSER}/g" /usr/local/etc/php-fpm.d/www.conf
RUN sed -i "s/group = www-data/group = ${PHPGROUP}/g" /usr/local/etc/php-fpm.d/www.conf

RUN mkdir -p /var/www/html/public

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

USER ${PHPUSER}

CMD ["php-fpm", "--nodaemonize"]