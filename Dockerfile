FROM php:8.5-apache

# Composer installs PHP libraries (PHPMailer, Stripe) into vendor/; unzip lets it unpack downloads.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN apt-get update && apt-get install -y --no-install-recommends unzip && rm -rf /var/lib/apt/lists/*

# Install the PHP extensions needed to connect to MariaDB.
RUN docker-php-ext-install pdo_mysql mysqli \
    && sed -i -E 's#/var/www/html(/public)?#/var/www/html/public#g' \
        /etc/apache2/sites-available/000-default.conf \
        /etc/apache2/apache2.conf \
    && a2enmod rewrite \
    && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' \
        /etc/apache2/apache2.conf