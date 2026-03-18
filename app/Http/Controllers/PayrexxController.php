<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PayrexxController extends Controller
{
    public function creerGateway(Request $request)
    {
        $request->validate([
            'montant' => 'required|numeric|min:0.01',
        ]);

        $instance = env('PAYREXX_INSTANCE');
        $apiKey   = env('PAYREXX_API_KEY');
        $montantCentimes = (int) round($request->montant * 100);

        $params = [
            'amount'             => $montantCentimes,
            'currency'           => 'CHF',
            'successRedirectUrl' => env('APP_URL') . '/inscriptions',
            'failedRedirectUrl'  => env('APP_URL') . '/panier',
            'cancelRedirectUrl'  => env('APP_URL') . '/panier',
        ];

        // Signature calculée sur tous les paramètres (méthode officielle SDK Payrexx)
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

        return response()->json([
            'url' => $data['data'][0]['link'] ?? null,
        ]);
    }
}