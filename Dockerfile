FROM php:8.1-apache

# Instalar extensões PHP necessárias
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Habilitar mod_rewrite para URLs amigáveis
RUN a2enmod rewrite

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install mbstring exif pcntl bcmath gd

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html

# Expor porta 80
EXPOSE 80 