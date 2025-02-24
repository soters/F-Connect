# Use PHP CLI (no Apache or NGINX needed)
FROM php:8.2-cli

# Set working directory
WORKDIR /var/www/html

# Install required libraries and PHP extensions for Azure SQL
RUN apt-get update && apt-get install -y \
    gnupg2 \
    unixodbc \
    unixodbc-dev \
    odbcinst \
    libgssapi-krb5-2 \
    libodbc1 \
    curl \
    && curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add - \
    && curl https://packages.microsoft.com/config/debian/12/prod.list > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y msodbcsql18 mssql-tools18 \
    && apt-get install -y php-pear php-dev \
    && pecl install pdo_sqlsrv \
    && docker-php-ext-enable pdo_sqlsrv

# Copy project files
COPY . /var/www/html/

# Expose port 8000 (for Railway)
EXPOSE 8000

# Run PHP built-in server
CMD ["php", "-S", "0.0.0.0:8000"]
