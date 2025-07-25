FROM php:8.3-cli

ARG APP_DIR=/var/www/html

ARG REDIS_LIB_VERSION=5.3.7

RUN apt-get update -y && apt-get install -y --no-install-recommends \
    apt-utils \
    supervisor \
    zlib1g-dev \
    libzip-dev \
    unzip \
    libpng-dev \
    libpq-dev \
    libxml2-dev \
    curl \
    nano \
    git

RUN docker-php-ext-install mysqli pdo pdo_mysql pdo_pgsql pgsql session xml zip iconv simplexml pcntl gd fileinfo

RUN pecl install redis-${REDIS_LIB_VERSION} && docker-php-ext-enable redis

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR $APP_DIR

COPY --chown=www-data:www-data . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN cp .env.example .env || true

RUN php artisan key:generate --force

RUN chown -R www-data:www-data $APP_DIR && \
    chmod -R 755 $APP_DIR/storage $APP_DIR/bootstrap/cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

EXPOSE 80

CMD ["sh", "-c", "php artisan migrate --force && php artisan config:cache && /usr/bin/supervisord -c /etc/supervisor/supervisord.conf"]