# ============================================================
#  Shanti Lanka Ashram
#  Apache + PHP 8.3, para que el .htaccess del proyecto
#  surta efecto igual que en el hosting.
# ============================================================
FROM php:8.3-apache

# Extensiones que pide el sitio: pdo_sqlite, mbstring y gd
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j"$(nproc)" gd mbstring pdo_sqlite \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Sin AllowOverride, Apache ignoraría el .htaccess
RUN printf '<Directory /var/www/html>\n\
    Options -Indexes +FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n' > /etc/apache2/conf-available/shanti.conf \
    && a2enconf shanti

# El panel admite imágenes de hasta 8 MB; PHP por defecto corta en 2 MB
RUN printf 'upload_max_filesize = 10M\n\
post_max_size = 12M\n\
memory_limit = 256M\n\
max_file_uploads = 20\n\
expose_php = Off\n' > /usr/local/etc/php/conf.d/shanti.ini

COPY --chown=www-data:www-data . /var/www/html

# La base de datos y las imágenes subidas se escriben en tiempo de ejecución
RUN mkdir -p /var/www/html/data /var/www/html/images/subidas \
    && chown -R www-data:www-data /var/www/html/data /var/www/html/images/subidas

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD php -r 'exit(@file_get_contents("http://127.0.0.1/") === false ? 1 : 0);'

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
