# Étape 1 : Utiliser l'image PHP de base avec FPM
FROM php:8.2-fpm

# Étape 2 : Installer les dépendances système requises
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libonig-dev \
    libzip-dev \
    # Installer l'extension MySQL (pdo_mysql)
    libmariadb-dev \
    && rm -rf /var/lib/apt/lists/*

# Étape 3 : Installer les extensions PHP nécessaires (incluant celle pour MySQL)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath opcache

# Étape 4 : Définir le répertoire de travail dans le conteneur
WORKDIR /var/www/html

# Étape 5 : Copier les fichiers de l'application
COPY . /var/www/html

# Étape 6 : Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Étape 7 : Installer les dépendances Laravel
RUN composer install --no-dev --optimize-autoloader

# Étape 8 : Configurer les permissions et clés
# Cette étape est cruciale pour le bon fonctionnement de Laravel
RUN php artisan key:generate
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Étape 9 : Exposer le port de PHP-FPM
EXPOSE 9000

# Le conteneur exécutera PHP-FPM, qui doit être servi par un serveur web (généralement Nginx, que Render gère implicitement pour les conteneurs FPM).
CMD ["php-fpm"]