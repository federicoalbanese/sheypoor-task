FROM php:8.4-cli-alpine

RUN apk update  \
    && apk add dcron \
    && apk add zip icu-dev libxml2-dev \
    && apk add --no-cache pcre-dev $PHPIZE_DEPS \
    && apk add --no-cache supervisor \
    && apk add --update linux-headers \
    && apk add gettext \
    && apk add bash

RUN pecl install redis && docker-php-ext-enable redis

RUN docker-php-ext-install -j$(nproc) pdo pdo_mysql intl soap pcntl sockets

ARG UID
ARG GID
ARG PROC_NUM
ARG PROC_OFFLINE_SEARCH_NUM

ENV UID=${UID}
ENV GID=${GID}
ENV PHPGROUP=laravel
ENV PHPUSER=laravel
ENV PROC_NUM=${PROC_NUM}
ENV PROC_OFFLINE_SEARCH_NUM=${PROC_OFFLINE_SEARCH_NUM}

RUN addgroup -g ${GID} ${PHPGROUP}
RUN adduser -G ${PHPGROUP} -D -s /bin/sh -u ${UID} ${PHPUSER}

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY entrypoint.sh /init/entrypoint.sh
COPY envsubst.sh /init/envsubst.sh
COPY cron.sh /init/cron.sh

COPY tmpl/cron.tmpl /init/tmpl/cron.tmpl
COPY tmpl/horizon.tmpl /init/tmpl/horizon.tmpl

RUN mkdir -p /etc/supervisor/conf.d/

RUN chmod +x /init/envsubst.sh
RUN chmod +x /init/entrypoint.sh
RUN chmod +x /init/cron.sh

ENTRYPOINT ["/init/entrypoint.sh"]

CMD ["/usr/bin/supervisord", "-n", "-c",  "/etc/supervisor/my-supervisor.conf"]
