FROM php:7.3-apache
RUN apt-get update && apt-get install -y redis-server zip unzip git curl cron
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
COPY ./src/ /var/www/html/
COPY crontab /root/crontab
COPY container_start.sh /container_start.sh
WORKDIR /var/www/html
RUN composer install
EXPOSE 80
ENTRYPOINT "/container_start.sh"
