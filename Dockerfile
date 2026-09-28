FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install mbstring \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY docker/apache.conf /etc/apache2/conf-available/hmh.conf
COPY docker/uploads.ini /usr/local/etc/php/conf.d/uploads.ini
RUN a2enconf hmh

COPY docker/entrypoint.sh /usr/local/bin/hmh-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/hmh-entrypoint.sh \
    && chmod +x /usr/local/bin/hmh-entrypoint.sh

WORKDIR /var/www/html
COPY . /var/www/html
RUN cp /var/www/html/enviar-email.php.example /var/www/html/enviar-email.php \
    && mkdir -p /opt/hmh-seed \
    && cp -a /var/www/html/data /opt/hmh-seed/data \
    && cp -a /var/www/html/images /opt/hmh-seed/images \
    && mkdir -p /var/www/html/mensagens /var/www/html/config \
    && chown -R www-data:www-data /var/www/html/data /var/www/html/images /var/www/html/mensagens /var/www/html/config \
    && chown www-data:www-data /var/www/html/robots.txt /var/www/html/sitemap.xml

EXPOSE 10000
ENTRYPOINT ["hmh-entrypoint.sh"]
