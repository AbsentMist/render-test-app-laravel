# 🏃‍♂️ Running Geneva - Plateforme Web

Ce dépôt contient le code source de la nouvelle plateforme web pour Running Geneva, développée dans le cadre d'un projet de mandat HEG.

L'application utilise une architecture séparée combinant un backend Laravel et un frontend réactif en Vue.js.

---

## 🛠 Stack Technique

| Composant | Technologie | Configuration |
| :--- | :--- | :--- |
| **Frontend** | Vue.js 3, Tailwind CSS v4, Pinia | `package.json` |
| **Backend** | Laravel 12, PHP 8.2+ | `composer.json` |
| **Tests API** | PHPUnit (Base de données en mémoire) | `phpunit.xml` |
| **Tests UI** | Vitest avec Happy-DOM | `vite.config.js` |
| **Production** | Render (MariaDB) | `Dockerfile` |

---

## ⚙️ 1. Prérequis Système

Avant de commencer, votre machine doit disposer de **PHP (>= 8.2)**, **Composer**, et **Node.js (v20.x)**. L'installation diffère selon votre système d'exploitation.

### 🍎 / 🪟 Pour macOS et Windows
La méthode la plus rapide et recommandée est d'utiliser un environnement de développement pré-packagé qui inclut toutes les extensions PHP nécessaires :
* **macOS / Windows :** Installez [Laravel Herd](https://herd.laravel.com/).
* **Alternative Windows :** XAMPP ou Laragon (assurez-vous d'activer les extensions `zip`, `gd`, `pdo_sqlite` dans votre `php.ini`).
* **Node.js :** Installez la [version 20 LTS](https://nodejs.org/).

### 🐧 Pour Linux (Ubuntu / Debian)
Linux nécessite l'installation manuelle de PHP et de ses extensions pour faire tourner Laravel et l'exportation de fichiers. Exécutez les commandes suivantes dans votre terminal :

**Installation de PHP 8.2 et extensions :**

`sudo apt update`

`sudo apt install php8.2 php8.2-cli php8.2-fpm php8.2-sqlite3 php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath`

**Installation de Node.js 20 et NPM :**

`curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -`

`sudo apt-get install -y nodejs`

**Installation de Composer :**
Consultez le [site officiel de Composer](https://getcomposer.org/download/) pour l'installer globalement.

---

## 🚀 2. Installation du projet

Ces étapes sont à réaliser uniquement la première fois que vous clonez le projet.

**1. Cloner le dépôt et entrer dans le dossier**

`git clone https://github.com/AbsentMist/render-test-app-laravel.git`

`cd render-test-app-laravel`

**2. Installer les dépendances Backend et Frontend**

`composer install`

`npm install`

`npm run build`

**3. Configurer l'environnement local**

`cp .env.example .env`

`php artisan key:generate`

*Note : Le fichier .env.example est déjà préconfiguré pour le développement local. Les clés API externes (Payrexx) utilisent des valeurs factices et l'envoi d'emails est défini sur log (les emails ne sont pas expédiés mais stockés localement dans storage/logs/laravel.log).*

**4. Configurer la base de données et générer les données de test**
Créez le fichier de base de données physique (SQLite):

`touch database/database.sqlite`

Lancez les migrations pour construire la base :

`php artisan migrate:fresh --seed`

**Utilisateurs de test**
L'exécution de la commande précédente a généré des profils par défaut pour vous permettre de tester les différents rôles de l'application immédiatement. 

| Rôle | Email de connexion | Mot de passe |
| :--- | :--- | :--- |
| **Administrateur** | admin1@inscriptionrunning.ch | `Admin123#` |
| **Administrateur** | admin2@inscriptionrunning.ch | `Admin123#` |
| **Participant** | particip1@inscriptionrunning.ch | `Particip123#` |
| **Participant** | particip2@inscriptionrunning.ch | `Particip123#` |


---

## 💻 3. Lancer l'application en local

L'application nécessite que le serveur API (Backend) et le serveur de développement Vite (Frontend) tournent en même temps.

**1. Démarrer le Backend (API)**
Ouvrez un terminal et exécutez :

`php artisan serve --port=8888`

**2. Démarrer le Frontend**
Ouvrez un *second* terminal et exécutez :

`npm run dev`

L'application sera accessible sur **http://127.0.0.1:8888** ou via l'URL locale fournie par Vite. 

### ⚠️ Erreur "Failed to listen on 127.0.0.1:8888" ou Port déjà utilisé
Si le port 8888 est déjà occupé par une autre application ou si le serveur refuse de se lancer, appliquez la méthode alternative suivante :

**1. Vérifier dans le fichier .env**
Assurez-vous d'avoir configuré ces variables :

`SERVER_HOST=127.0.0.1`

`SERVER_PORT=8888`

**2. Forcer la mise à jour des configurations**
Dans votre terminal, exécutez :

`php artisan config:clear`

`php artisan optimize:clear`

**3. Lancer le serveur via PHP manuellement**
Si `php artisan serve` rencontre toujours un problème, contournez-le en utilisant directement le serveur natif de PHP :

`php -S localhost:8888 -t public`


---

## 🧪 4. Exécuter les tests

Le projet est configuré pour des tests automatisés stricts des deux côtés de l'application. La configuration des tests backend utilise une base de données SQLite en mémoire pour une exécution ultra-rapide.

**Lancer les tests Backend (PHPUnit) :**
La commande de test s'assure d'abord de vider le cache de configuration pour éviter les conflits d'environnement.
`composer run test`
*(Ou directement : `php artisan test`)*

**Lancer les tests Frontend (Vitest) :**
La suite de tests Vue.js utilise Happy-DOM pour simuler le navigateur.
Si la dépendance Happy-DOM n'est pas encore installée, lancez d'abord :

`npm install -D happy-dom`

`npm run test`