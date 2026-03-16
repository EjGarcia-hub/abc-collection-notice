FROM php:8.2-apache

# Install PostgreSQL PDO driver + SSL certs
RUN apt-get update && apt-get install -y \
    libpq-dev \
    ca-certificates \
 && docker-php-ext-install pdo pdo_pgsql \
 && a2enmod rewrite \
 && rm -rf /var/lib/apt/lists/*

# Allow .htaccess and use index.php by default
RUN printf '%s\n' \
    '<Directory /var/www/html>' \
    '    AllowOverride All' \
    '    Require all granted' \
    '    DirectoryIndex index.php index.html index.htm' \
    '</Directory>' \
    > /etc/apache2/conf-available/app.conf \
 && a2enconf app

# Copy app
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
