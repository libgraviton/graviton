FROM php:7.4-cli

ARG EXTMONGODB_VERSION=1.10.0
ARG EXTAMQP_VERSION=1.10.2

RUN apt update && \
    apt-get install -y librabbitmq-dev git zip unzip && \
    printf "\n" | pecl install -f mongodb-${EXTMONGODB_VERSION} && \
    printf "\n" | pecl install -f amqp-${EXTAMQP_VERSION} && \
    printf "\n" | pecl install -f xdebug && \
    docker-php-ext-enable mongodb && \
    docker-php-ext-enable amqp && \
    docker-php-ext-enable xdebug && \
    apt clean

COPY --from=composer:2 --chown=www-data:root /usr/bin/composer /usr/local/bin/composer
COPY entrypoint.sh /entrypoint.sh

RUN chmod +x /usr/local/bin/composer /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
