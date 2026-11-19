# =====================================================================
#  GUIAR - imagem PHP 8.2 + Apache
#  Document-root padrao do php:8.2-apache = /var/www/html (compativel
#  com a estrutura do projeto, que serve index.html, routes.php e
#  config.php a partir da raiz).
# =====================================================================
FROM php:8.2-apache

# Extensoes PHP exigidas pelo projeto: PDO MySQL (banco) e mbstring.
# libonig-dev fornece oniguruma, exigido pelo mbstring (regex multibyte).
# unzip atende o composer na extracao de pacotes.
RUN apt-get update && apt-get install -y --no-install-recommends \
        unzip \
        libonig-dev \
    && docker-php-ext-install pdo_mysql mbstring \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Modulo do Apache para reescrita de URL (caso seja usado .htaccess).
RUN a2enmod rewrite

# Composer para instalar o PHPMailer.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Instala as dependencias PHP antes de copiar o resto (cache de camadas).
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Codigo da aplicacao (o .dockerignore filtra o que nao entra na imagem).
COPY . .

# Garante que a area de uploads seja gravavel pelo Apache.
# Em runtime, ./public/uploads do host e montado por volume no compose.
RUN mkdir -p public/uploads \
    && chown -R www-data:www-data public/uploads

EXPOSE 80
