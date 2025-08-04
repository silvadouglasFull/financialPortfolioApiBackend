FROM php:8.4-fpm

# Arguments
ARG user=financialPortfolioApiBackend
ARG uid=1000

# Instala dependências básicas do sistema e extensões do PHP necessárias para Laravel + MySQL + e-mail
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    nano \
    cron \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libmariadb-dev-compat \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# O restante do seu Dockerfile permanece o mesmo
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY crontab.txt /etc/cron.d/crontab
RUN chmod 0644 /etc/cron.d/crontab
RUN crontab /etc/cron.d/crontab
RUN useradd -G www-data,root -u $uid -d /home/$user $user && \
    mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user
WORKDIR /var/www
RUN mkdir -p storage bootstrap/cache \
    && chown -R $user:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

USER $user