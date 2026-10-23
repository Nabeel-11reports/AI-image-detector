FROM ubuntu:24.04

ENV DEBIAN_FRONTEND=noninteractive
ENV APACHE_DOCUMENT_ROOT=/var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        apache2 \
        libapache2-mod-php \
        php \
        php-cli \
        php-gd \
        php-mysql \
        php-mbstring \
        php-xml \
        mariadb-server \
        supervisor \
        ca-certificates \
        file \
        curl \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite headers

COPY app/ /var/www/html/
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/start.sh /usr/local/bin/start-lamp.sh

RUN chmod +x /usr/local/bin/start-lamp.sh \
    && rm -f /var/www/html/index.html \
    && mkdir -p /var/www/html/uploads /run/mysqld \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chown mysql:mysql /run/mysqld

EXPOSE 80
CMD ["/usr/local/bin/start-lamp.sh"]
