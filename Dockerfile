# NHUTIN Portal - PHP + Apache cho Render
FROM php:8.2-apache

# Bật mod_rewrite
RUN a2enmod rewrite headers

# Cài PDO PostgreSQL cho Render Postgres
RUN docker-php-ext-install pdo pdo_pgsql

# Copy toàn bộ project
COPY . /var/www/html/

# Cho phép .htaccess
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Render dùng PORT động - entrypoint chỉnh Apache listen đúng port
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
ENTRYPOINT ["docker-entrypoint.sh"]
