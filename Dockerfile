# Use PHP 8.2 CLI as the base image
FROM php:8.2-cli

# Install required dependencies
RUN apt-get update && apt-get install -y \
    gnupg2 \
    unixodbc \
    unixodbc-dev \
    odbcinst \
    libgssapi-krb5-2 \
    libodbc1 \
    curl \
    apt-transport-https \
    software-properties-common \
    && curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /usr/share/keyrings/microsoft.gpg \
    && echo "deb [signed-by=/usr/share/keyrings/microsoft.gpg] https://packages.microsoft.com/debian/12/prod bookworm main" | tee /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update

# Install Microsoft ODBC Driver
RUN ACCEPT_EULA=Y apt-get install -y msodbcsql18

# Install required PHP development tools and pdo_sqlsrv
RUN docker-php-ext-install pdo pdo_mysql \
    && pecl install pdo_sqlsrv \
    && docker-php-ext-enable pdo_sqlsrv
