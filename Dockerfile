# Use official PHP image
FROM php:8.2-apache

# Copy project files to Apache web root
COPY . /var/www/html/

# Enable Apache rewrite module and install mysqli
RUN docker-php-ext-install mysqli && a2enmod rewrite

# Expose port 80
EXPOSE 80
