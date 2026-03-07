<?php

namespace App\Http\Controllers;

use App\Models\Groupe;
use App\Models\Participant;
use App\Enums\StatutParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Str;

class GroupeController extends Controller
{
    // ==========================================
    // CRUD CLASSIQUE (GROUPES)
    // ==========================================

    public function index()
    {
        $idParticipant = Auth::user()->participant->id;

        // Récupère les groupes où le participant connecté est membre, fondateur ou en attente
        $groupes = Groupe::whereHas('participants', function($query) use ($idParticipant) {
            $query->where('id_participant', $idParticipant);
        })->with('participants')->get();

        return response()->json($groupes);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nom' => 'required|string|max:100',
            'type' => 'required|string', 
        ]);

        // Générer le code entreprise automatiquement si type "Entreprise"
        if ($validatedData['type'] === 'Entreprise') {
            
            $prefixe = 'E-';
            
            //Code unique
            do {
                $code = $prefixe . strtoupper(Str::random(7));
            } while (Groupe::where('code_entreprise', $code)->exists());
            
            $validatedData['code_entreprise'] = $code;

        } else {
            $validatedData['code_entreprise'] = null;
        }

        $idParticipant = Auth::user()->participant->id;

        
        $groupe = Groupe::create($validatedData);

        // Obtiens automatiquement le statut fondateur du groupe
        $groupe->participants()->attach($idParticipant, [
            'statut' => StatutParticipant::FONDATEUR->value
        ]);

        return response()->json($groupe->load('participants'), 201);
    }

    public function show($id)
    {
        $groupe = Groupe::with('participants')->findOrFail($id);
        return response()->json($groupe);
    }

    public function update(Request $request, $id)
    {
        $groupe = Groupe::findOrFail($id);
        $idParticipant = Auth::user()->participant->id;

        //Fondateur uniquement
        $isFondateur = $groupe->participants()
            ->where('id_participant', $idParticipant)
            ->where('GroupeParticipant.statut', StatutParticipant::FONDATEUR->value)
            ->exists();

        if (!$isFondateur) {
            return response()->json(['message' => 'Non autorisé. Seul le fondateur peut modifier ce groupe.'], 403);
        }

        $validatedData = $request->validate([
            'nom' => 'sometimes|required|string|max:100',
            'code_entreprise' => 'nullable|string|max:255',
        ]);

        $groupe->update($validatedData);

        return response()->json($groupe->load('participants'));
    }

    public function destroy($id)
    {
        $groupe = Groupe::findOrFail($id);
        $idParticipant = Auth::user()->participant->id;

        $isFondateur = $groupe->participants()
            ->where('id_participant', $idParticipant)
            ->where('GroupeParticipant.statut', StatutParticipant::FONDATEUR->value)
            ->exists();

        if (!$isFondateur) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        
        $groupe->participants()->detach();
        $groupe->delete();

        return response()->json(['message' => 'Groupe supprimé avec succès.']);
    }

    
    // GESTION DES MEMBRES 

    // Ajouter un participant existant 
    public function addParticipant(Request $request, $idGroupe)
    {
        $validatedData = $request->validate([
            'id_participant' => 'required|exists:Participant,id',
            
        ]);

        $groupe = Groupe::findOrFail($idGroupe);

        if ($groupe->participants()->where('id_participant', $validatedData['id_participant'])->exists()) {
            return response()->json(['message' => 'Ce participant est déjà dans le groupe.'], 409);
        }

        // Ajout avec statut "en_attente" car c'est une invitation
        $groupe->participants()->attach($validatedData['id_participant'], [
            'statut' => StatutParticipant::EN_ATTENTE->value
        ]);

        return response()->json([
            'message' => 'Invitation envoyée (Participant ajouté en attente).',
            'groupe' => $groupe->load('participants')
        ]);
    }

    // Retirer un participant
    public function removeParticipant($idGroupe, $idParticipant)
    {
        $groupe = Groupe::findOrFail($idGroupe);
        
        // Sécurité : Seul le fondateur ou le membre lui-même peut se retirer
        $groupe->participants()->detach($idParticipant);

        return response()->json([
            'message' => 'Participant retiré du groupe.',
            'groupe' => $groupe->load('participants')
        ]);
    }

    // VALIDATION CODE ENTREPRISE LORS DU PANIER (2.2 & 5.1)

    public function verifierCodeEntreprise(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $idParticipant = Auth::user()->participant->id;

        //Cherche si un groupe possède ce code
        $groupe = Groupe::where('code_entreprise', $request->code)->first();

        if (!$groupe) {
            return response()->json([
                'valide' => false, 
                'message' => 'Ce code de participation est invalide.'
            ], 404);
        }

        //Vérifie si le participant fait partie de ce groupe 
        $estMembre = $groupe->participants()->where('id_participant', $idParticipant)->exists();

        if (!$estMembre) {
            return response()->json([
                'valide' => false, 
                'message' => 'Vous ne faites pas partie du groupe associé à ce code.'
            ], 403);
        }

        //Envoie de l'information au frontend pour validation du panier
        return response()->json([
            'valide' => true,
            'message' => 'Code appliqué avec succès !',
            'groupe' => $groupe->only(['id', 'nom', 'type']) 
        ], 200);
    }

    //Gérer le cas où le participant n'existe pas dans le système (2.4)
}