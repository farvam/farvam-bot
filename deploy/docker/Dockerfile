# panel-market: Farvam marketing site (PHP 8.3 + Apache + SQLite), served under /farvam/
FROM php:8.3-apache

# GD with WebP lets the admin panel convert uploaded JPG/PNG to WebP.
# Optional: if the package mirror is unreachable the build continues (upload WebP directly then).
RUN a2enmod rewrite remoteip headers expires deflate \
 && ( apt-get update \
      && apt-get install -y --no-install-recommends libpng-dev libjpeg62-turbo-dev libwebp-dev libfreetype6-dev \
      && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
      && docker-php-ext-install -j"$(nproc)" gd \
      || echo "WARNING: GD not installed (package mirror unreachable); WebP conversion disabled" ) \
 && rm -rf /var/lib/apt/lists/*

COPY docker/php-panel-market.ini /usr/local/etc/php/conf.d/zz-panel-market.ini
COPY docker/apache-panel-market.conf /etc/apache2/conf-enabled/zz-panel-market.conf
COPY docker/snapshot.php docker/import-json.php docker/entrypoint.sh /opt/panel-market/
COPY site/ /var/www/html/farvam/
RUN printf '<?php header("Location: /farvam/", true, 302);\n' > /var/www/html/index.php \
 && mkdir -p /var/lib/panel-market \
 && chown -R www-data:www-data /var/www/html/farvam /var/lib/panel-market \
 && find /var/www/html/farvam -type d -exec chmod 755 {} + \
 && find /var/www/html/farvam -type f -exec chmod 644 {} + \
 && mkdir -p /opt/panel-market/defaults/assets \
 && cp -a /var/www/html/farvam/images /var/www/html/farvam/files /opt/panel-market/defaults/ \
 && cp -a /var/www/html/farvam/assets/fonts /opt/panel-market/defaults/assets/ \
 && chmod 755 /opt/panel-market/entrypoint.sh

# SQLite database (settings, texts, articles, leads); its own volume, outside the web root
ENV FARVAM_DB=/var/lib/panel-market/panel-market.sqlite

ENTRYPOINT ["/opt/panel-market/entrypoint.sh"]
CMD ["apache2-foreground"]

HEALTHCHECK --interval=30s --timeout=5s --retries=3 CMD php -r 'exit(@file_get_contents("http://127.0.0.1/farvam/") === false ? 1 : 0);'
