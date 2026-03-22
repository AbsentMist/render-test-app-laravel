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
            'nom'       => 'required|string|max:100',
            'type'      => 'required|string',
            'id_course' => 'nullable|exists:Course,id', // ← ajouter
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

        //Différence entre l'ajout d'un profil rattaché au compte (sous-profil) et un utilisateur invité
        $participant = Participant::find($validatedData['id_participant']);
        $estGereParLeCompte = $participant && $participant->id_user === Auth::id();

        // Ajout avec statut "en_attente" car c'est une invitation,
        // SAUF pour les profils rattachés au compte
        $statut = $estGereParLeCompte ? 'Membre' : StatutParticipant::EN_ATTENTE->value;

        $groupe->participants()->attach($validatedData['id_participant'], [
            'statut' => $statut
        ]);

        return response()->json([
            'message' => $estGereParLeCompte ? 'Participant ajouté en tant que membre directement.' : 'Invitation envoyée (Participant ajouté en attente).',
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

    // GESTION DES INVITATIONS 2.4

    // Récupère les invitations en attente pour l'utilisateur connecté
    public function getInvitations()
    {
        $idParticipant = Auth::user()->participant->id;

        
        $invitations = Groupe::whereIn('id', function($query) use ($idParticipant) {
            $query->select('id_groupe')
                  ->from('GroupeParticipant') // On cible explicitement la table pivot
                  ->where('id_participant', $idParticipant)
                  ->where('statut', StatutParticipant::EN_ATTENTE->value);
        })
        ->with('participants') 
        ->get();

        return response()->json($invitations);
    }

    // Acceptation d'une invitation
    public function accepterInvitation($idGroupe)
    {
        $idParticipant = Auth::user()->participant->id;
        $groupe = Groupe::findOrFail($idGroupe);

        // On met à jour le statut dans la table d'association
        $groupe->participants()->updateExistingPivot($idParticipant, [
            'statut' => 'Membre' //Passe de "En attente" à "Membre"
        ]);

        // On met à jour le statut des inscriptions liées au groupe(Fondateur ET Membre)
        \App\Models\Inscription::where('id_groupe', $idGroupe)
            ->update(['status_paiement' => 'Validé']);

        return response()->json([
            'message' => 'Invitation acceptée avec succès.',
            'groupe' => $groupe
        ], 200);
    }

    // Refus d'une invitation
    public function refuserInvitation($idGroupe)
    {
        $participantConnecte = Auth::user()->participant;
        $idParticipant = $participantConnecte->id;
        $groupe = Groupe::findOrFail($idGroupe);

        // Retire le participant de la table GroupeParticipant
        $groupe->participants()->detach($idParticipant);

        // Annule l'inscription de l'invité pour cette course
        \App\Models\Inscription::where('id_participant', $idParticipant)
            ->where('id_groupe', $idGroupe)
            ->update(['status_paiement' => 'Annulé']);

        // Cherche le fondateur du groupe pour le prévenir
        $fondateur = $groupe->participants()->wherePivot('statut', 'fondateur')->first();

        if ($fondateur && $fondateur->user) {
            try {
                // Le mail part dans le fichier laravel.log pour le moment (à implémenter l'envoi réel plus tard)
                \Illuminate\Support\Facades\Mail::send('emails.invitation_refusee', [
                    'fondateur' => $fondateur,
                    'invite' => $participantConnecte,
                    'groupe' => $groupe
                ], function ($message) use ($fondateur) {
                    $message->to($fondateur->user->email)
                            ->subject('Une invitation à votre équipe a été refusée');
                });
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Email refus impossible : " . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Invitation refusée. Votre inscription a été annulée.'
        ], 200);
    }

}