# Use official PHP image
FROM php:8.2-apache

# Copy project files to Apache web root
COPY . /var/www/html/

# Enable Apache rewrite module and install mysqli + PostgreSQL driver
RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install pdo pdo_pgsql mysqli && a2enmod rewrite


# Expose port 80
EXPOSE 80
