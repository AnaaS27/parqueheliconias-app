# Imagen oficial de PHP con servidor embebido + PostgreSQL
FROM php:8.2-cli

# Instalar extensión de PostgreSQL
RUN docker-php-ext-install pgsql pdo pdo_pgsql

# Copiar archivos del proyecto al contenedor
COPY . /var/www/html

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Exponer el puerto 10000
EXPOSE 10000

# Comando de inicio: servidor PHP nativo
CMD ["php", "-S", "0.0.0.0:10000", "-t", "."]
