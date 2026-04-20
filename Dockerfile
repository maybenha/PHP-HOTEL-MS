FROM php:8.2-apache

# Enable extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy project
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/