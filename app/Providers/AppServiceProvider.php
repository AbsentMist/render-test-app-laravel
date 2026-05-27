<?php

/**
 * @fileoverview AppServiceProvider.php
 * @description Service provider principal de l'application.
 *              Contient deux configurations critiques pour le déploiement :
 *              - Limite la longueur des colonnes string à 191 caractères pour assurer
 *                la compatibilité avec MySQL < 5.7.7 (index utf8mb4 limité à 767 bytes).
 *              - Force le schéma HTTPS uniquement en production (Render.com)
 *                pour éviter les mixed-content et rediriger correctement les callbacks Payrexx.
 * @author Ngoie Steven
 */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Enregistre les services de l'application.
     * Aucun binding personnalisé pour l'instant.
     * @author Ngoie Steven
     */
    public function register(): void
    {
        //
    }

    /**
     * Démarre les services de l'application après l'initialisation du framework.
     * - defaultStringLength(191) : fix de compatibilité MySQL < 5.7.7 pour les migrations
     *   utilisant des colonnes string comme index (utf8mb4, 4 bytes/char × 191 = 764 bytes < 767).
     * - forceScheme('https') : force les URLs générées (redirects, assets, callbacks)
     *   en HTTPS uniquement sur l'environnement de production Render.
     * @author Ngoie Steven
     */
    public function boot(): void
    {
        // Nécessaire pour éviter "Specified key was too long" lors des migrations sur MySQL < 5.7.7
        Schema::defaultStringLength(191);

        // Force HTTPS uniquement en production pour garantir la sécurité et les redirects Payrexx
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}