<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use Illuminate\Http\Request;

class EvenementController extends Controller
{
    // GET (admin): 
    public function indexAdmin()
    {
        $evenements = Evenement::all();
        return response()->json($evenements);
    }

    // GET (participant): 
    public function indexParticipant()
    {
        // Uniquement les événements actifs, sans les champs d'administration
        $evenements = Evenement::where('is_actif', true)
            ->select(
                'id', 
                'nom', 
                'logo', 
                'site', 
                'couleur_primaire', 
                'couleur_secondaire'
            )
            ->get();

        return response()->json($evenements);
    }

    // POST : (Admin)
    public function store(Request $request)
    {
        $checkEvent = $request->validate([
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

        // Si image récupèrer son code binaire
        if ($request->hasFile('logo')) {
            $validatedData['logo'] = file_get_contents($request->file('logo')->getRealPath());
        }

        $evenement = Evenement::create($checkEvent);

        return response()->json([
            'message' => 'Évènement créé avec succès.',
            'evenement' => $evenement
        ], 201);
    }

    // GET : (Admin)
    public function show($id)
    {
        $evenement = Evenement::findOrFail($id);
        return response()->json($evenement);
    }

    // PUT/PATCH : (Admin)
    public function update(Request $request, $id)
    {
        $evenement = Evenement::findOrFail($id);

        $checkEvent = $request->validate([
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

        $evenement->update($checkEvent);

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