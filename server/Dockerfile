FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip libzip-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/server

# Создаем необходимые директории и даем права
RUN mkdir -p bootstrap/cache storage/framework/{sessions,views,cache} storage/logs
RUN chmod -R 777 bootstrap/cache storage

COPY . .

# Устанавливаем права после копирования
RUN chown -R www-data:www-data /var/www/server \
    && chmod -R 777 storage \
    && chmod -R 777 bootstrap/cache

RUN composer install --optimize-autoloader --no-dev

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
