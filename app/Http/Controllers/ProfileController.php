<?php

/**
 * @fileoverview ProfileController.php
 * @description Contrôleur gérant la consultation et la mise à jour du profil principal
 *              du participant connecté. Gère les conversions de format entre le frontend
 *              (date en d/m/Y, adresse et numéro séparés) et la base de données
 *              (date en Y-m-d, adresse concaténée, photo en BLOB).
 * @author Ngoie Steven
 */

namespace App\Http\Controllers;

use App\Models\Participant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Retourne le profil complet du participant connecté dans le format attendu par le frontend.
     * Conversions appliquées :
     *   - date_naissance Y-m-d → d/m/Y
     *   - adresse concaténée → séparée en `adresse` et `numero` via regex
     *   - photo BLOB → base64 data URI
     * @author Ngoie Steven
     * @param  Request $request Requête authentifiée.
     * @return \Illuminate\Http\JsonResponse Profil formaté ou 404 si aucun participant lié.
     */
    public function show(Request $request)
    {
        $user        = $request->user()->load('participant');
        $participant = $user->participant;

        if (!$participant) {
            return response()->json(['message' => 'Participant introuvable.'], 404);
        }

        // Sépare l'adresse stockée en base ("Rue de la Paix 12") en rue + numéro
        [$adresse, $numero] = $this->splitAdresseNumero($participant->adresse);

        return response()->json([
            'nom'           => $participant->nom,
            'prenom'        => $participant->prenom,
            'email'         => $user->email,
            // Conversion de la date au format d/m/Y pour le formulaire frontend
            'dateNaissance' => $participant->date_naissance
                ? Carbon::parse($participant->date_naissance)->format('d/m/Y')
                : null,
            'adresse'       => $adresse,
            'numero'        => $numero,
            'club'          => $participant->equipe_nom,
            'npa'           => $participant->code_postal,
            'commune'       => $participant->ville,
            'nationalite'   => $participant->nationalite,
            'telephone'     => $participant->telephone,
            'tailleTshirt'  => $participant->taille_tshirt,
            // Conversion du BLOB photo en base64 pour l'affichage dans le frontend
            'photo'         => $participant->photo
                ? 'data:image/jpeg;base64,' . base64_encode($participant->photo)
                : null,
        ]);
    }

    /**
     * Met à jour le profil principal du participant connecté.
     * Conversions appliquées :
     *   - date d/m/Y → Y-m-d pour MySQL
     *   - adresse + numéro → concaténés en un seul champ
     *   - photo uploadée → BLOB
     * Retourne le profil mis à jour via show() pour garantir une réponse cohérente.
     * @author Ngoie Steven
     * @param  Request $request Données du profil à mettre à jour.
     * @return \Illuminate\Http\JsonResponse Profil mis à jour (via show).
     */
    public function update(Request $request)
    {
        $user        = $request->user()->load('participant');
        $participant = $user->participant;

        if (!$participant) {
            return response()->json(['message' => 'Participant introuvable.'], 404);
        }

        $validated = $request->validate([
            'nom'           => 'required|string|max:100',
            'prenom'        => 'required|string|max:100',
            // Ignore l'unicité email pour l'utilisateur courant afin d'éviter un faux conflit
            'email'         => [
                'required', 'email', 'max:80',
                Rule::unique('User', 'email')->ignore($user->id),
            ],
            'dateNaissance' => 'required|date_format:d/m/Y',
            'adresse'       => 'required|string|max:100',
            'numero'        => 'required|string|max:20',
            'club'          => 'nullable|string|max:100',
            'npa'           => 'required|string|max:10',
            'commune'       => 'required|string|max:100',
            'nationalite'   => 'required|string|max:100',
            // Ignore l'unicité téléphone pour le participant courant afin d'éviter un faux conflit
            'telephone'     => [
                'required', 'string', 'max:20',
                Rule::unique('Participant', 'telephone')->ignore($participant->id),
            ],
            'tailleTshirt'  => 'required|string|in:XS,S,M,L,XL,XXL',
            'photo'         => 'nullable|image|max:1024',
        ]);

        $user->update(['email' => $validated['email']]);

        $participantData = [
            'nom'           => $validated['nom'],
            'prenom'        => $validated['prenom'],
            // Conversion du format frontend d/m/Y vers le format MySQL Y-m-d
            'date_naissance'=> Carbon::createFromFormat('d/m/Y', $validated['dateNaissance'])->format('Y-m-d'),
            // Concatène adresse et numéro pour le stockage en base
            'adresse'       => trim($validated['adresse'] . ' ' . $validated['numero']),
            'equipe_nom'    => $validated['club'] ?? null,
            'code_postal'   => $validated['npa'],
            'ville'         => $validated['commune'],
            'pays'          => $validated['nationalite'],
            'nationalite'   => $validated['nationalite'],
            'telephone'     => $validated['telephone'],
            'taille_tshirt' => $validated['tailleTshirt'],
        ];

        // Convertit la photo en BLOB si un nouveau fichier est uploadé
        if ($request->hasFile('photo')) {
            $participantData['photo'] = file_get_contents($request->file('photo')->getRealPath());
        }

        $participant->update($participantData);

        // Réutilise show() pour retourner le profil avec les mêmes conversions de format
        return $this->show($request);
    }

    /**
     * Sépare une adresse concaténée en rue et numéro via expression régulière.
     * Supporte les numéros avec lettre (ex: "12A", "3bis").
     * Exemples :
     *   "Rue de la Paix 12"  → ["Rue de la Paix", "12"]
     *   "Avenue du Mont 12A" → ["Avenue du Mont", "12A"]
     *   "Sans numéro"        → ["Sans numéro", ""]
     * @author Ngoie Steven
     * @param  string|null $adresse Adresse complète à décomposer.
     * @return array{string, string} Tableau [rue, numéro].
     */
    private function splitAdresseNumero(?string $adresse): array
    {
        if (!$adresse) {
            return ['', ''];
        }

        // Cherche un numéro (chiffres + lettre optionnelle) en fin de chaîne
        if (preg_match('/^(.*)\s+(\d+[A-Za-z]?)$/', trim($adresse), $matches)) {
            return [trim($matches[1]), $matches[2]];
        }

        // Aucun numéro détecté : retourne l'adresse complète et un numéro vide
        return [$adresse, ''];
    }
}