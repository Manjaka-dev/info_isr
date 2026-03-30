FROM php:8.2.4-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libicu-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libwebp-dev \
        libfreetype6-dev \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql intl gd \
    && a2enmod rewrite expires deflate headers \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php/allow-override.conf /etc/apache2/conf-available/allow-override.conf
RUN a2enconf allow-override

# Compatibilite avec admin/.htaccess qui reference un chemin absolu XAMPP
RUN mkdir -p /Applications/XAMPP/xamppfiles/htdocs/optim/info_isr/admin \
    && ln -sf /var/www/html/optim/info_isr/admin/.htpasswd \
      /Applications/XAMPP/xamppfiles/htdocs/optim/info_isr/admin/.htpasswd

WORKDIR /var/www/html

