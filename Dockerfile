FROM php:8.2-fpm

ENV COMPOSER_MEMORY_LIMIT=-1
ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    cron \
    git \
    zip \
    unzip \
    awscli \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-install \
        pdo_mysql \
        bcmath \
        gmp \
        zip \
        gd \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/custom.ini \
 && echo "upload_max_filesize=1024M" >> /usr/local/etc/php/conf.d/custom.ini \
 && echo "post_max_size=512M" >> /usr/local/etc/php/conf.d/custom.ini \
 && echo "max_execution_time=180" >> /usr/local/etc/php/conf.d/custom.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN ln -sf /dev/stdout /var/log/nginx/access.log \
 && ln -sf /dev/stderr /var/log/nginx/error.log

RUN mkdir -p /usr/share/nginx/ibim-license-management
COPY . /usr/share/nginx/ibim-license-management
WORKDIR /usr/share/nginx/ibim-license-management

RUN composer install --no-dev --optimize-autoloader

RUN chmod -R 777 /usr/share/nginx/ibim-license-management/storage

COPY docker-setup/nginx.conf /etc/nginx/sites-enabled/default
COPY docker-setup/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker-setup/initial.sh /usr/bin/initial
RUN chmod +x /usr/bin/initial

COPY docker-setup/cronschedule /etc/cron.d/cronschedule
RUN chmod 0644 /etc/cron.d/cronschedule && crontab /etc/cron.d/cronschedule

COPY docker-setup/cron-startup.sh /cron-startup.sh
RUN chmod +x /cron-startup.sh

ENTRYPOINT ["initial"]
