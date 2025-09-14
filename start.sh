#!/bin/sh

# Aguardar banco de dados estar disponível
echo "Aguardando banco de dados..."
until nc -z db 3306; do
    echo "Banco não disponível ainda, aguardando..."
    sleep 2
done
echo "Banco de dados disponível!"

# Executar otimizações do Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
# Verificar se precisa rodar migrations
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Executando migrations..."
    php artisan migrate --force
fi

# Criar diretórios de log se não existirem
mkdir -p /var/log/nginx
touch /var/log/nginx/access.log
touch /var/log/nginx/error.log
touch /var/log/php_errors.log
touch /var/log/fpm-php.www.log

# Iniciar PHP-FPM em background
php-fpm -D

# Iniciar Nginx em foreground
nginx -g 'daemon off;'
