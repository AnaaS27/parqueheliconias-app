# Imagen oficial de PHP con servidor CLI
FROM php:8.2-cli

# Instalar dependencias del sistema necesarias para PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libssl-dev \
    build-essential

# Instalar extensiones de PostgreSQL para PHP
RUN docker-php-ext-install pgsql pdo pdo_pgsql

# Copiar archivos del proyecto
COPY . /var/www/html

# Directorio de trabajo
WORKDIR /var/www/html

# Exponer puerto 10000
EXPOSE 10000

# Comando de inicio
CMD ["php", "-S", "0.0.0.0:10000", "-t", "."]
