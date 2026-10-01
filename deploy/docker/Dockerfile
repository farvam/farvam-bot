# Farvam marketing site: PHP 8.3 + Apache, served under /farvam/
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

COPY docker/php-farvam.ini /usr/local/etc/php/conf.d/zz-farvam.ini
COPY docker/apache-farvam.conf /etc/apache2/conf-enabled/zz-farvam.conf
COPY site/ /var/www/html/farvam/
RUN printf '<?php header("Location: /farvam/", true, 302);\n' > /var/www/html/index.php \
 && chown -R www-data:www-data /var/www/html/farvam \
 && find /var/www/html/farvam -type d -exec chmod 755 {} + \
 && find /var/www/html/farvam -type f -exec chmod 644 {} +

HEALTHCHECK --interval=30s --timeout=5s --retries=3 CMD php -r 'exit(@file_get_contents("http://127.0.0.1/farvam/") === false ? 1 : 0);'
