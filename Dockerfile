# Dockerfile para o serviço Laravel (PHP-FPM + Nginx + Composer)
# Usa a imagem base oficial do PHP 8.2 com FPM
FROM php:8.2-fpm

# Define o diretório de trabalho dentro do container
WORKDIR /var/www

# Instala as dependências do sistema necessárias para o Laravel e extensões PHP
# (gd para manipulação de imagem, pdo_mysql para conexão com o banco de dados, etc.)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Instala o Composer globalmente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copia os arquivos de configuração do Nginx personalizados
COPY docker-compose/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker-compose/nginx/sites-available/default /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default \
    && rm -rf /etc/nginx/sites-available/default

# Expõe a porta 9000 para o PHP-FPM e 80 para o Nginx
EXPOSE 9000 80

# Inicia o PHP-FPM e o Nginx
# Adiciona um script de entrada para gerenciar permissões e iniciar ambos os serviços
COPY docker-compose/laravel/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Define o script de entrada como o ponto de execução principal do container
ENTRYPOINT ["entrypoint.sh"]