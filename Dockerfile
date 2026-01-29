FROM ubuntu:20.04
ENV DEBIAN_FRONTEND=noninteractive
ENV COMPOSER_MEMORY_LIMIT=-1

RUN apt-get update \
    && apt-get install -y software-properties-common curl zip unzip git awscli supervisor cron nginx \
    && add-apt-repository ppa:ondrej/php -y

RUN apt-get update && apt-get install -y \
    php8.2 \
    php8.2-fpm \
    php8.2-cli \
    php8.2-curl \
    php8.2-gmp \
    php8.2-bcmath \
    php8.2-mysql \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-gd \
    php8.2-opcache \
    php8.2-zip \
    php8.2-dev \
    wget \
    build-essential \
    php-pear \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN pecl install redis \
    && echo "extension=redis.so" > /etc/php/8.2/cli/conf.d/20-redis.ini \
    && echo "extension=redis.so" > /etc/php/8.2/fpm/conf.d/20-redis.ini

RUN sed -i "s/max_execution_time = .*/max_execution_time = 180/" /etc/php/8.2/cli/php.ini \
 && sed -i "s/memory_limit = .*/memory_limit = 512M/" /etc/php/8.2/cli/php.ini \
 && sed -i "s/upload_max_filesize = .*/upload_max_filesize = 1024M/" /etc/php/8.2/cli/php.ini \
 && sed -i "s/post_max_size = .*/post_max_size = 512M/" /etc/php/8.2/cli/php.ini

RUN php -r "readfile('https://getcomposer.org/installer');" | php -- \
    --install-dir=/usr/bin/ \
    --filename=composer

RUN ln -sf /dev/stdout /var/log/nginx/access.log \
 && ln -sf /dev/stderr /var/log/nginx/error.log

RUN mkdir -p /usr/share/nginx/ibim-license-management
COPY . /usr/share/nginx/ibim-license-management
WORKDIR /usr/share/nginx/ibim-license-management

RUN composer install --no-dev --optimize-autoloader

RUN chmod -R 777 /usr/share/nginx/ibim-license-management/storage

ADD docker-setup/nginx.conf /etc/nginx/sites-enabled/default
ADD docker-setup/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
ADD docker-setup/initial.sh /usr/bin/initial
RUN chmod +x /usr/bin/initial

COPY docker-setup/cronschedule /etc/cron.d/cronschedule
RUN chmod 0644 /etc/cron.d/cronschedule && crontab /etc/cron.d/cronschedule

COPY docker-setup/cron-startup.sh /cron-startup.sh
RUN chmod +x /cron-startup.sh

RUN rm -f /run/php/php7.4-fpm.sock

ENTRYPOINT ["initial"]
