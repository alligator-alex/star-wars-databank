FROM php:8.4-fpm-alpine

ARG USER_ID
ARG GROUP_ID

ENV USER_ID=${USER_ID?1000}
ENV GROUP_ID=${GROUP_ID?1000}

RUN set -ex && \
    apk --no-cache --update add \
        linux-headers \
        git \
        postgresql \
        postgresql-dev \
        libxml2-dev \
        oniguruma-dev \
        libzip-dev \
        bash \
        nodejs \
        npm

RUN docker-php-ext-install \
    opcache \
    pdo \
    pdo_pgsql \
    ctype \
    simplexml \
    bcmath \
    fileinfo \
    mbstring \
    sockets \
    xml \
    zip \
    intl

RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install \
      xdebug-3.4.0 \
      redis \
    && docker-php-ext-enable \
      xdebug \
      redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY ./configs/php.ini $PHP_INI_DIR/conf.d/
COPY ./configs/php-fpm.conf $PHP_INI_DIR/php-fpm.conf

WORKDIR /var/www

RUN apk add shadow && \
    usermod -u $USER_ID -s /bin/ash www-data && \
    groupmod -g $GROUP_ID www-data

CMD ["php-fpm"]

EXPOSE 9000
