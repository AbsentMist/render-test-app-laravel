<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use Illuminate\Http\Request;

class EvenementController extends Controller
{
    // GET (admin): 
    public function indexAdmin()
    {
        $evenements = Evenement::all()->map(function ($evenement) {
            // Transformation du BLOB en Base64 pour l'affichage Frontend
            if ($evenement->logo) {
                $evenement->logo = 'data:image/jpeg;base64,' . base64_encode($evenement->logo);
            }
            return $evenement;
        });

        return response()->json($evenements);
    }

    // GET (participant): 
    public function indexParticipant()
    {
        
        $evenements = Evenement::where('is_actif', true)
            ->select(
                'id', 
                'nom', 
                'logo', 
                'site', 
                'couleur_primaire', 
                'couleur_secondaire'
            )
            ->get()
            ->map(function ($evenement) {
                // Transformation du BLOB en Base64 (pour le frontend)
                if ($evenement->logo) {
                    $evenement->logo = 'data:image/jpeg;base64,' . base64_encode($evenement->logo);
                }
                return $evenement;
            });

        return response()->json($evenements);
    }

    // POST : (Admin)
    public function store(Request $request)
    {
        // On stocke tout dans $validatedData
        $validatedData = $request->validate([
            'nom' => 'required|string|max:180',
            'logo' => 'nullable|file|image|max:2048', 
            'site' => 'nullable|string|max:255',
            'couleur_primaire' => 'nullable|string|max:50',
            'couleur_secondaire' => 'nullable|string|max:50',
            'is_avertissement' => 'boolean',
            'is_document' => 'boolean',
            'is_questionnaire' => 'boolean',
            'is_rabais' => 'boolean',
            'is_actif' => 'boolean',
            'is_interne' => 'boolean',
        ]);

        // Si une image, convertir en BLOB pour la base de données
        if ($request->hasFile('logo')) {
            $validatedData['logo'] = file_get_contents($request->file('logo')->getRealPath());
        }

        
        $evenement = Evenement::create($validatedData);

        return response()->json([
            'message' => 'Évènement créé avec succès.',
            'evenement' => $evenement
        ], 201);
    }

    // GET : (Admin)
    public function show($id)
    {
        $evenement = Evenement::findOrFail($id);

        
        if ($evenement->logo) {
            $evenement->logo = 'data:image/jpeg;base64,' . base64_encode($evenement->logo);
        }

        return response()->json($evenement);
    }

    // PUT/PATCH : (Admin)
    public function update(Request $request, $id)
    {
        $evenement = Evenement::findOrFail($id);

        $validatedData = $request->validate([
            'nom' => 'sometimes|string|max:180',
            'logo' => 'nullable|file|image|max:2048', 
            'site' => 'nullable|string|max:255',
            'couleur_primaire' => 'nullable|string|max:50',
            'couleur_secondaire' => 'nullable|string|max:50',
            'is_avertissement' => 'boolean',
            'is_document' => 'boolean',
            'is_questionnaire' => 'boolean',
            'is_rabais' => 'boolean',
            'is_actif' => 'boolean',
            'is_interne' => 'boolean',
        ]);

        
        if ($request->hasFile('logo')) {
            $validatedData['logo'] = file_get_contents($request->file('logo')->getRealPath());
        }

        $evenement->update($validatedData);

        return response()->json([
            'message' => 'Évènement mis à jour avec succès.',
            'evenement' => $evenement
        ]);
    }

    // DELETE : (Admin uniquement)
    public function destroy($id)
    {
        $evenement = Evenement::findOrFail($id);
        $evenement->delete();

        return response()->json([
            'message' => 'Évènement supprimé avec succès.'
        ]);
    }
}