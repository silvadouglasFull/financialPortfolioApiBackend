FROM php:8.2-fpm

# Instala pacotes do sistema
RUN apt-get update && apt-get install -y \
    nginx \
    cron \
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
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copia configuração do Nginx
COPY ./docker-compose/nginx/travellist.conf /etc/nginx/nginx.conf

# Copia o crontab e configura
COPY crontab.txt /etc/cron.d/crontab
RUN chmod 0644 /etc/cron.d/crontab && crontab /etc/cron.d/crontab

# Cria usuário para rodar app (opcional)
ARG user=financialPortfolioApiBackend
ARG uid=1000
RUN useradd -G www-data,root -u $uid -d /home/$user $user \
    && mkdir -p /home/$user/.composer \
    && chown -R $user:$user /home/$user

#Copia os arquivos
COPY . /var/www

#Dando permissões corretas
RUN chown -R $user:www-data /var/www/storage && chmod -R 775 /var/www/storage

# Define diretório de trabalho
WORKDIR /var/www

# Expondo a porta esperada pelo Fly.io
EXPOSE 8080

# Script de inicialização do container
CMD service cron start && \
    php-fpm & \
    nginx -g 'daemon off;'
