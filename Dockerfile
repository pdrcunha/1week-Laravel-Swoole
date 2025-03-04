# Baseado no Swoole para alta performance com PHP 8.3
FROM phpswoole/swoole:php8.3

# Instala extensões necessárias para Laravel
RUN docker-php-ext-install pdo pdo_pgsql pcntl

# Instala Node.js diretamente na imagem PHP
RUN apt-get update && apt-get install -y nodejs npm && npm install -g chokidar-cli

# Define diretório de trabalho
WORKDIR /var/www

# Copia arquivos e instala dependências do Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY . .
RUN composer install

# Permissões para armazenamento e cache
RUN chmod -R 775 storage bootstrap/cache

# Expõe porta padrão do Swoole
EXPOSE 9501

# Comando para iniciar Laravel com Swoole e watcher
CMD ["php", "artisan", "octane:start", "--watch", "--server=swoole", "--host=0.0.0.0", "--port=9501"]
