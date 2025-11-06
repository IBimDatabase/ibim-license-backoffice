FROM ubuntu:20.04
ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install -y curl zip unzip git awscli supervisor cron
RUN apt-get install -y nginx
RUN apt-get update && apt-get install -y php php-fpm php-cli php-curl php-gmp php-bcmath \
    php-mysql php-mbstring php-xml php-gd php-opcache php-json php-dom php-zip \
    wget \
    build-essential \
    php-dev \
    php-pear \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN pecl install redis && \
    echo "extension=redis.so" > /etc/php/7.4/cli/conf.d/20-redis.ini && \
    echo "extension=redis.so" > /etc/php/7.4/fpm/conf.d/20-redis.ini

RUN php -v

RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log

RUN sed -i "s/max_execution_time = .*/max_execution_time = 180/" /etc/php/7.4/cli/php.ini
RUN sed -i "s/memory_limit = .*/memory_limit = 512M/" /etc/php/7.4/cli/php.ini
RUN sed -i "s/upload_max_filesize = .*/upload_max_filesize = 1024M/" /etc/php/7.4/cli/php.ini
RUN sed -i "s/post_max_size = .*/post_max_size = 512M/" /etc/php/7.4/cli/php.ini

RUN mkdir /usr/share/nginx/ibim-license-management
COPY . /usr/share/nginx/ibim-license-management
WORKDIR /usr/share/nginx/ibim-license-management

# Install app dependencies
RUN composer install

RUN chmod -R 777 /usr/share/nginx/ibim-license-management/storage

ADD docker-setup/nginx.conf /etc/nginx/sites-enabled/default
ADD docker-setup/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
ADD docker-setup/initial.sh /usr/bin/initial
RUN chmod +x /usr/bin/initial

COPY docker-setup/cronschedule /etc/cron.d/
RUN chmod 777 /etc/cron.d/cronschedule
RUN crontab /etc/cron.d/cronschedule

COPY docker-setup/cron-startup.sh /
RUN chmod +x /cron-startup.sh


ENTRYPOINT [ "initial" ]
