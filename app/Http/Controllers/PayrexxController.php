<?php

/**
 * @fileoverview PayrexxController.php
 * @description Intégration de la passerelle de paiement Payrexx (alternative à PostFinance).
 *
 * @status EN ATTENTE D'ACTIVATION
 * @remarks Ce contrôleur a été développé et est fonctionnel, mais n'a pas pu être activé
 *          faute des informations nécessaires de la part de l'association.
 *
 *          L'intégration requiert un compte Payrexx actif au nom de l'association RunningGeneva,
 *          ainsi que les clés API associées. Sans ces éléments, le système continue de fonctionner
 *          avec la solution PostFinance actuellement en place.
 *
 *          Pour activer cette intégration à l'avenir :
 *          1. Créer ou récupérer le compte Payrexx de l'association sur https://payrexx.com
 *          2. Récupérer l'instance name et la clé API dans le dashboard Payrexx
 *          3. Renseigner ces valeurs dans le fichier .env :
 *             PAYREXX_INSTANCE=nom_instance_runningeneva
 *             PAYREXX_API_KEY=cle_api_runningeneva
 *          4. Brancher les routes correspondantes dans api.php
 *
 * @author Guillermet Jean-Daniel
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PayrexxController extends Controller
{
    /**
     * Crée une session de paiement Payrexx (gateway) et retourne l'URL de redirection.
     * Le montant est converti en centimes (format attendu par l'API Payrexx).
     * La signature HMAC-SHA256 est calculée selon la méthode officielle du SDK Payrexx.
     * @author Guillermet Jean-Daniel
     * @param  \Illuminate\Http\Request $request Doit contenir `montant` (numeric, min: 0.01 CHF).
     * @return \Illuminate\Http\JsonResponse URL de la page de paiement Payrexx, ou 500 en cas d'erreur.
     */
    public function creerGateway(Request $request)
    {
        $request->validate([
            'montant' => 'required|numeric|min:0.01',
        ]);

        $instance        = env('PAYREXX_INSTANCE');
        $apiKey          = env('PAYREXX_API_KEY');
        // Payrexx attend le montant en centimes sous forme d'entier (ex: 25.00 CHF → 2500)
        $montantCentimes = (int) round($request->montant * 100);

        $params = [
            'amount'             => $montantCentimes,
            'currency'           => 'CHF',
            'successRedirectUrl' => env('APP_URL') . '/inscriptions',
            'failedRedirectUrl'  => env('APP_URL') . '/panier',
            'cancelRedirectUrl'  => env('APP_URL') . '/panier',
        ];

        // Signature calculée sur tous les paramètres selon la méthode officielle du SDK Payrexx
        $params['ApiSignature'] = base64_encode(
            hash_hmac('sha256', http_build_query($params, '', '&'), $apiKey, true)
        );

        $response = Http::asForm()->post(
            "https://api.payrexx.com/v1.0/Gateway/?instance={$instance}",
            $params
        );

        if ($response->failed()) {
            \Log::error('Payrexx error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return response()->json([
                'message' => 'Impossible de créer la session de paiement.',
                'debug'   => $response->body(),
            ], 500);
        }

        $data = $response->json();

        // Retourne l'URL de la page de paiement générée par Payrexx
        return response()->json([
            'url' => $data['data'][0]['link'] ?? null,
        ]);
    }
}