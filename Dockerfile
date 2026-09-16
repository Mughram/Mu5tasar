FROM php:8.4-apache-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev ca-certificates \
    && docker-php-ext-install pdo_pgsql \
    && a2enmod headers \
    && rm -rf /var/lib/apt/lists/*

ENV PORT=8080 \
    SESSION_COOKIE_SECURE=1 \
    DB_SSLMODE=verify-full \
    DB_SSLROOTCERT=/etc/ssl/certs/ca-certificates.crt

WORKDIR /var/www/mu5tasar
COPY config/ ./config/
COPY main/ ./main/
COPY main/.user.ini /usr/local/etc/php/conf.d/mu5tasar.ini
COPY deploy/apache.conf /etc/apache2/sites-available/000-default.conf
COPY deploy/ports.conf /etc/apache2/ports.conf
COPY deploy/server.conf /etc/apache2/conf-enabled/mu5tasar.conf

RUN chown www-data:www-data main/uploads \
    && chmod 0755 main/uploads

EXPOSE 8080
CMD ["apache2-foreground"]
