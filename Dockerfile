FROM php:8.3-fpm

# 安裝系統依賴
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev

# 清理 apt 快取
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# 安裝 PHP 擴展
RUN docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd zip

# 安裝 Redis 擴展
RUN pecl install redis && docker-php-ext-enable redis

# 設定工作目錄
WORKDIR /var/www

# 安裝 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 設定使用者權限
RUN chown -R www-data:www-data /var/www

# 設定 PHP 配置
COPY ./docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

# 啟動 PHP-FPM
CMD ["php-fpm"]

EXPOSE 9000