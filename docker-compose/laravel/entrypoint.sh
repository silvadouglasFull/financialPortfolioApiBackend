#!/bin/bash
# entrypoint.sh

# Define as permissões corretas para os diretórios do Laravel
# Permissões de escrita para storage e bootstrap/cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Instala as dependências do Composer, se o diretório vendor não existir
if [ ! -d "/var/www/vendor" ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Inicia o PHP-FPM em segundo plano
php-fpm &

# Inicia o Nginx em primeiro plano para manter o container rodando
nginx -g "daemon off;"
