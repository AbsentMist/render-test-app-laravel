# Étape 1 : Image de base PHP avec FPM
FROM php:8.2-fpm

# Étape 2 : Dépendances système + INSTALLATION DE NODE.JS + Dépendances pour GD (utile pour l'exportExcel)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libonig-dev \
    libzip-dev \
    libmariadb-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Étape 3 : Extensions PHP (Ajout de la configuration et de l'installation de GD)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath opcache gd zip

# Étape 4 : Répertoire de travail
WORKDIR /var/www/html

# Étape 5 : Copier les fichiers
COPY . /var/www/html

# Étape 6 : Composer (Backend)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Étape 7 : NPM (Frontend) - COMPILATION VUE & TAILWIND
RUN npm install
RUN npm run build

# Étape 8 : Permissions pour Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Étape 9 : Expose & Lancement
EXPOSE 8000
# On force la migration au démarrage pour que la DB MariaDB de Render soit toujours à jour
# On ajoute également des données pour que l'application possède déjà les accès admins
CMD php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=8000