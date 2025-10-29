FROM php:8.4-fpm

ARG user=laravel
ARG uid=1000

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip sockets

# Install Redis extension via PECL
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create non-root user
RUN adduser --disabled-password --gecos "" --uid $uid $user \
    && usermod -aG www-data $user

WORKDIR /var/www

# Copy custom PHP configs
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

# RUN chown -R $user:www-data /var/www && \
#     chmod -R 775 /var/www/storage /var/www/bootstrap/cache

USER $user
