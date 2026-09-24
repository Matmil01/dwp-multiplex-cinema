FROM php:8.3-apache

# Install the PHP extensions needed to connect to MariaDB.
RUN docker-php-ext-install pdo_mysql mysqli \
    && sed -i -E 's#/var/www/html(/public)?#/var/www/html/public#g' \
        /etc/apache2/sites-available/000-default.conf \
        /etc/apache2/apache2.conf