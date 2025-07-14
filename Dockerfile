FROM php:8.4-fpm

# Arguments
ARG user=financialPortfolioApiBackend
ARG uid=1000

# Instala dependências básicas do sistema e extensões do PHP necessárias para Laravel + SQLite + e-mail
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
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_sqlite mbstring exif pcntl bcmath gd zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

#crontab
# Copia seu crontab para o container
COPY crontab.txt /etc/cron.d/crontab

# Permissões do crontab
RUN chmod 0644 /etc/cron.d/crontab

# Aplica o crontab
RUN crontab /etc/cron.d/crontab

# Cria usuário para rodar os comandos do Laravel
RUN useradd -G www-data,root -u $uid -d /home/$user $user && \
    mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Define diretório de trabalho
WORKDIR /var/www

# Define o usuário não-root
USER $user

RUN composer install --no-dev --optimize-autoloader
