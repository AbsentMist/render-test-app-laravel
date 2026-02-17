# 🏃‍♂️ Running Geneva - Plateforme Web

Ce dépôt contient le code source de la nouvelle plateforme web pour Running Geneva, développée dans le cadre d'un  projet de mandat.

Ce projet utilise une architecture moderne combinant un backend **Laravel** puissant et un frontend réactif en **Vue.js**.

---

## 🛠 Stack Technique

* **Frontend :** Vue.js 3, Pinia (State management), Tailwind CSS v4, Flowbite, Vue Router
* **Backend :** Laravel 12, PHP 8.2+
* **Paiement :** SDK Payrexx
* **Base de données (Local) :** SQLite (pour un développement rapide sans serveur lourd)
* **Déploiement (Production) :** Render synchronisé sur la branche `main` avec MariaDB

---

##  1. Installation initiale (À faire une seule fois)

Ces étapes sont à réaliser **uniquement la première fois** que vous clonez le projet sur votre ordinateur.

### Prérequis
Assurez-vous d'avoir installé sur votre machine :
* **PHP (version 8.2 ou supérieure)**
    ```bash
    php -v
    ```
    *(Si absent : [Télécharger PHP](https://www.php.net/downloads))*

* **Composer (Gestionnaire de paquets PHP)**
    ```bash
    composer -V
    ```
    *(Si absent : [Télécharger Composer](https://getcomposer.org/))*

* **Node.js et NPM (Gestionnaires de paquets Javascript)**
    ```bash
    node -v
    npm -v
    ```
    *(Si absent : [Télécharger Node.js](https://nodejs.org/))*

### Récupérer le projet et les dépendances
Ouvrez votre terminal et exécutez ces commandes :
```bash
# 1. Cloner le dépôt
git clone https://github.com/AbsentMist/render-test-app-laravel.git
cd render-test-app-laravel

# 2. Installer les dépendances Backend (PHP)
composer install

# 3. Installer les dépendances Frontend (Javascript/Vue)
npm install
```

--- 

##  2. Configurer l'environnement local ".env" (À faire une seule fois)

Le fichier `.env` contient les réglages propres à chaque machine. Il est ignoré par Git pour des raisons de sécurité, il faut donc le recréer manuellement.

1. **Créer le fichier .env** :
   ```bash
   cp .env.example .env
   ```
2. **Générer la clé de sécurité :**
    ```bash
   php artisan key:generate
   ```

   ---
   ##  3. Configurer la base de données locale
    1. **Créer le fichier pour la base de données** :
   ```bash
   ni database/database.sqlite -type file
   ```
   ou clique droit sur répertoire **database** et créer un fichier "database.sqlite" dans l'explorateur de fichier

   2. **Lancer les migrations** :
   ```bash
   php artisan migrate
   ```

---

##  4. Lancer le projet
Lancer le frontend : 
```bash
   npm run dev
   ```

Lancer le backend : 
```bash
   php artisan serve
   ```
L'application sera alors accessible sur http://127.0.0.1:8000.

### Erreur "Failed to listen on 127.0.0.1:8000"
1. **Se rendre dans le .env et écrire** :
   ```bash
   SERVER_HOST=127.0.0.1
   SERVER_PORT=8888
   ```
2. **Forcer la mise à jour** :
   ```bash
   php artisan config:clear
   php artisan optimize:clear
   ```
3. **Lancer le serveur via PHP manuellement** :
    ```bash
   php -S localhost:8888 -t public
   ```


   
