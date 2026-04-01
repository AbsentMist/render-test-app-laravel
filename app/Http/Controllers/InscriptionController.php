<?php

namespace App\Http\Controllers;

use App\Models\Inscription;
use App\Models\Course;
use App\Models\Dossard;
use App\Models\ChoixOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Participant;
use App\Models\Groupe;

class InscriptionController extends Controller
{
    //GET (ADMIN)
    public function indexAdmin()
    {
        //Vérifie si c'est un admin
        $user = Auth::user();
        if (!$user->roles()->where('type', 'Administrateur')->exists()) {
            return response()->json(['message' => 'Accès non autorisé. Réservé aux administrateurs.'], 403);
        }

        $inscriptions = Inscription::with(['course.evenement', 'participant', 'dossard', 'groupe','choixOptions.option', 'reponsesQuestions.question', 'reponsesQuestions.option', 'documentsFournis', 'ancienneInscription.course', 'ancienneInscription.participant', 'ancienneInscription.groupe'])->orderBy('date_paiement', 'desc')->get();

        return response()->json($inscriptions);
    }

    //GET (PARTICIPANT)
    public function indexParticipant()
    {
        $user = Auth::user();

        //Récupération de l'ID du participant
        $idParticipant = $user->participant->id;

        //Renvoie uniquement les inscriptions du participant
        $inscriptions = Inscription::with(['course.evenement', 'dossard', 'groupe', 'participant','choixOptions.option', 'reponsesQuestions.question', 'documentsFournis', 'ancienneInscription.course', 'ancienneInscription.participant', 'ancienneInscription.groupe'])
            ->where('id_participant', $idParticipant)
            ->get();

        return response()->json($inscriptions);
    }

    //POST (PARTICIPANT)
    public function store(Request $request)
    {
          \Log::info('Données reçues:', $request->all());
        // Validation des données selon la DB
        $validatedData = $request->validate([
            'id_course' => 'required|exists:Course,id',
            'id_participant' => 'nullable|exists:Participant,id',
            'id_groupe' => 'nullable|exists:Groupe,id',
            'id_document' => 'nullable|exists:Document,id',
            'id_ancienne_inscription' => 'nullable|exists:Inscription,id',
            'code_participant' => 'nullable|string|unique:Inscription,code_participant',
            'avertissement_valide' => 'sometimes|boolean',
            //Ajout du champ "en attente" pour les inscriptions relais / entreprises
            'status_paiement'     => 'sometimes|in:Validé,Annulé,En attente',
            'participe_challenge' => 'sometimes|boolean',
            'type_challenge'      => 'nullable|string|max:50',
            'equipe_challenge'    => 'nullable|string|max:100',
            'date_paiement'       => now(),
            'tarif'               => 'sometimes|numeric', // Ajout du champ tarif
        ]);
        

        $idParticipantConnecte = Auth::user()->participant->id;
        $idParticipant = $request->id_participant ?? $idParticipantConnecte;
        $course = Course::findOrFail($validatedData['id_course']);

        // Différenciation entre inscription individuelle et inscription en groupe
        $participant = Participant::find($idParticipant);
        // On vérifie si ce participant est rattaché au compte connecté
        $estGereParLeCompte = $participant && $participant->id_user === Auth::id();

        // Règle : Lorsque l'inscription est en groupe (avec des utilisateurs existants dans le système)
        // tout le monde commence "En attente" jusqu'à ce que chaque membre accepte l'invitation
        // Sauf les profils rattachés directement au compte (qui ne possèdent pas de compte et
        // qui sont validés automatiquement)
        if (!empty($validatedData['id_groupe']) && !$estGereParLeCompte) {
            $statutInscription = 'En attente';
        } else {
            $statutInscription = 'Validé';
        }

        $statutPaiementFinal = $validatedData['status_paiement'] ?? $statutInscription;
        
        // Gestion erreur si la course à un avertissement obligatoire
        if ($course->is_avertissement == 1 && empty($validatedData['avertissement_valide'])) {
            return response()->json([
                'message' => 'Vous devez accepter les conditions/avertissements liés à cette course pour vous inscrire.'
            ], 422);
        }

        // Vérifie si le participant est déjà inscrit pour éviter les doublons
        $inscriptionExistante = Inscription::where('id_participant', $idParticipant)
            ->where('id_course', $course->id)
            ->first();

        if ($inscriptionExistante) {
            //Gestion erreur, si inscription déjà existante et que le statut de paiement est "En attente" ou "Validé", on bloque la réinscription
            if (in_array($inscriptionExistante->status_paiement, ['En attente', 'Validé'])) {
                return response()->json([
                    'message' => 'Vous êtes déjà inscrit à cette course (ou votre inscription est en attente de paiement).'
                ], 409);
            }

            //Réinscription du participant si l'inscription précédente avait été annulée
            if ($inscriptionExistante->status_paiement === 'Annulé') {
                $inscriptionExistante->update([
                    'id_groupe' => $validatedData['id_groupe'] ?? null,
                    'id_document' => $validatedData['id_document'] ?? null,
                    'id_ancienne_inscription' => $validatedData['id_ancienne_inscription'] ?? null,
                    'code_participant' => $validatedData['code_participant'] ?? null,
                    'tarif' => $course->tarif,
                    'date_paiement' => now(),
                    'status_paiement' => $statutPaiementFinal, // Application du statut dynamique
                    'montant_rabais' => 0,
                    'avertissement_valide' => $validatedData['avertissement_valide'] ?? false,
                ]);

                // GÉNÉRATION DOSSARD (Cas de la réinscription)
                if ($course->is_dossard == 0 && !$inscriptionExistante->dossard) {
                    try {
                        $this->genererDossardAutomatique($course, $inscriptionExistante->id);
                    } catch (\Exception $e) {
                        // Annulation de la réinscription car plus de dossard
                        $inscriptionExistante->update(['status_paiement' => 'Annulé']);
                        return response()->json([
                            'code' => 'DOSSARD_LIMIT_REACHED',
                            'message' => $e->getMessage()
                        ], 400);
                    }
                }

                // On renvoie 200 (OK) au lieu de 201 car modification
                return response()->json($inscriptionExistante->load(['course', 'dossard']), 200);
            }
        }

        // Création de l'inscription (s'il n'y avait aucun historique)
        // Création de l'inscription (s'il n'y avait aucun historique)
        $inscription = Inscription::create([
            'id_participant' => $idParticipant,
            'id_course' => $course->id,
            'id_groupe' => $validatedData['id_groupe'] ?? null,
            'id_document' => $validatedData['id_document'] ?? null,
            'id_ancienne_inscription' => $validatedData['id_ancienne_inscription'] ?? null,
            'code_participant' => $validatedData['code_participant'] ?? null,
            // On prend le prix de l'inscription avec les options choisies, sinon prix de base de la course
            'tarif' => $validatedData['tarif'] ?? $course->tarif,
            'date_paiement'       => now(),
            'status_paiement' => $statutPaiementFinal, // Application du statut dynamique
            'montant_rabais' => 0,
            'avertissement_valide' => $validatedData['avertissement_valide'] ?? false,
            'participe_challenge'  => $validatedData['participe_challenge'] ?? false,
            'type_challenge'       => $validatedData['type_challenge'] ?? null,
            'equipe_challenge'     => $validatedData['equipe_challenge'] ?? null,
        ]);

        // ANTICIPATION DOSSARD (À développer plus tard)
        // Si la course gère les dossards automatiquement, on pourrait le créer ici.
        // if ($course->is_dossard == 1 && $configurationOrganisateur == 'auto') {
        //     Dossard::create([...]);
        // }
        
        // GÉNÉRATION DOSSARD (par défaut si l'option de personnalisation est désactivée dans le formulaire course)
        if ($course->is_dossard == 0) {
            try {
                $this->genererDossardAutomatique($course, $inscription->id);
            } catch (\Exception $e) {
                // Gestion d'erreur critique : on supprime l'inscription qu'on vient de créer pour ne pas fausser la base
                $inscription->delete();
                
                return response()->json([
                    'code' => 'DOSSARD_LIMIT_REACHED',
                    'message' => $e->getMessage()
                ], 400);
            }
        }

        return response()->json($inscription->load(['course', 'dossard']), 201);
    }

    /**
     * MÉTHODE PRIVÉE : Logique isolée pour générer le dossard
     */
    private function genererDossardAutomatique($course, $idInscription)
    {
        // Tolérance : Si l'organisateur a laissé null/0, on fixe des limites par défaut pour éviter le plantage
        $premier = ($course->premier_dossard && $course->premier_dossard > 0) ? $course->premier_dossard : 1;
        $dernier = ($course->dernier_dossard && $course->dernier_dossard > 0) ? $course->dernier_dossard : 99999;

        // Chercher le numéro le plus élevé dans la table Dossard qui a déjà attribué pour cette course
        $maxNumeroActuel = Dossard::whereHas('inscription', function ($query) use ($course) {
            $query->where('id_course', $course->id);
        })->max('numero');

        // S'il y a déjà des inscrits, on fait max + 1. Sinon on prend le premier dossard de la course
        $prochainNumero = $maxNumeroActuel ? $maxNumeroActuel + 1 : $premier;

        // Sécurité : on s'assure qu'on a pas dépassé la limite de la course
        if ($prochainNumero <= $dernier) {
            Dossard::create([
                'numero' => $prochainNumero,
                'id_inscription' => $idInscription,
                'retrait_dossard' => 0
            ]);
        } else {
            // Gestion d'erreur : plus de dossard disponible
            // Selon la logique métier, on remonte une erreur à l'inscription afin de bloquer l'inscription.
            throw new \Exception("Désolé, il n'y a plus de dossards disponibles pour cette course (Limite fixée à {$dernier}).");
        }
    }

    //GET (PARTICIPANT & ADMIN)
    public function show($id)
    {
        $inscription = Inscription::with([
            'course.evenement',
            'participant',
            'dossard',
            'groupe',
            'choixOptions.option',
            'reponsesQuestions.question',
            'reponsesQuestions.option',
            'documentsFournis',
            'ancienneInscription.course',
            'ancienneInscription.participant',
            'ancienneInscription.groupe',

        ])->findOrFail($id);

        $user = Auth::user();
        $isAdmin = $user->roles()->where('type', 'Administrateur')->exists();

        if (!$isAdmin && $inscription->id_participant !== $user->participant->id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        return response()->json($inscription);
    }

   //PUT (ADMIN)
    public function updateAdmin(Request $request, $id)
    {
        $inscription = Inscription::findOrFail($id);

        // Protection métier : Un admin ne doit pas modifier le statut d'une inscription annulée
        if ($inscription->status_paiement === 'Annulé' && $request->has('status_paiement') && $request->status_paiement !== 'Annulé') {
            return response()->json([
                'message' => 'Impossible de modifier le statut de paiement d\'une inscription annulée.'
            ], 400);
        }

        $validatedData = $request->validate([
            'status_paiement' => 'sometimes|in:Validé,En attente,Annulé',
            'tarif' => 'sometimes|numeric',
            'montant_rabais' => 'sometimes|numeric',
            'avertissement_valide' => 'sometimes|boolean',
            'code_participant' => 'sometimes|nullable|string|unique:Inscription,code_participant,' . $inscription->id,
            'id_document' => 'sometimes|nullable|exists:Document,id',
            'id_groupe' => 'sometimes|nullable|exists:Groupe,id',
            'id_ancienne_inscription' => 'sometimes|nullable|exists:Inscription,id',
            'date_paiement' => 'sometimes|date',
            'id_course' => 'sometimes|exists:Course,id',
        ]);

        $inscription->update($validatedData);

        return response()->json($inscription->load(['course', 'participant', 'dossard']));
    }

    //DELETE (ADMIN)
    public function destroyAdmin($id)
    {
        $inscription = Inscription::findOrFail($id);
        
        $inscription->delete();

        return response()->json(['message' => 'Inscription supprimée avec succès.']);
    }

    //PUT (PARTICIPANT)
    public function updateParticipant(Request $request, $id)
    {
        $user = Auth::user();
        $inscription = Inscription::findOrFail($id);

        // Gestion erreur : un participant ne peut modifier que sa propre inscription
        if ($inscription->id_participant !== $user->participant->id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        // Modification des informations simples
        $validatedData = $request->validate([
            'id_groupe' => 'sometimes|nullable|exists:Groupe,id',
            'id_document' => 'sometimes|nullable|exists:Document,id',
            'code_participant' => 'sometimes|nullable|string|unique:Inscription,code_participant,' . $inscription->id,
            'choix_options' => 'sometimes|array',
            'choix_options.*.id_option' => 'required_with:choix_options|exists:Options,id',
            'choix_options.*.quantite' => 'sometimes|integer|min:0',
        ]);

        // Mise à jour des champs simples
        $inscription->update(array_intersect_key($validatedData, array_flip(['id_groupe', 'id_document', 'code_participant'])));

        // Mise à jour des options : supprimer tous les anciens et créer les nouveaux
        if (isset($validatedData['choix_options'])) {
            ChoixOption::where('id_inscription', $inscription->id)->delete();
            
            foreach ($validatedData['choix_options'] as $choix) {
                ChoixOption::create([
                    'id_inscription' => $inscription->id,
                    'id_option' => $choix['id_option'],
                    'quantite' => $choix['quantite'] ?? null,
                ]);
            }
        }

        return response()->json($inscription->load(['course', 'dossard', 'groupe', 'choixOptions.option', 'documentsFournis']));
    }

    //DELETE (PARTICIPANT) : Modifie le statut de paiement à "Annulé" au lieu de supprimer l'inscription
    public function destroyParticipant(Request $request, $id) 
    {
        $user = Auth::user();
        $inscription = Inscription::findOrFail($id);
        
        if ($inscription->id_participant !== $user->participant->id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        // Vérifie si c'est un changement de course (Upgrade/Downgrade)
        $isChange = $request->query('is_change') == 1;

        // Gestion erreur sur le statut de paiement
        // On autorise l'annulation d'une course payée UNIQUEMENT si c'est un changement
        if ($inscription->status_paiement === 'Validé' && !$isChange) {
            return response()->json([
                'message' => 'Impossible d\'annuler une inscription déjà payée. Veuillez contacter l\'organisateur pour toute demande d\'annulation ou de remboursement.'
            ], 400);
        }

        // Changement du statut à 'Annulé' au lieu d'une suppression en base de données (réservé à l'admin)
        $inscription->update(['status_paiement' => 'Annulé']);

        return response()->json([
            'message' => 'Inscription annulée avec succès.',
            'inscription' => $inscription
        ]);
    }
}