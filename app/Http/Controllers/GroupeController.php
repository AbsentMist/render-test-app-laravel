<?php

/**
 * @fileoverview GroupeController.php
 * @description Contrôleur gérant les groupes de participants (relais, entreprise, challenge) :
 *              création, lecture, modification, suppression, gestion des membres,
 *              validation du code entreprise et système d'invitations avec notifications.
 * @author Ngoie Steven
 */

namespace App\Http\Controllers;

use App\Models\Groupe;
use App\Models\Participant;
use App\Models\Message;
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

    /**
     * Retourne tous les groupes auxquels le participant connecté appartient,
     * quel que soit son statut (fondateur, membre ou en attente).
     * @author Ngoie Steven
     * @author Guillermet Jean-Daniel (chargement des relations participants et course)
     * @return \Illuminate\Http\JsonResponse Liste des groupes du participant avec leurs membres et courses.
     */
    public function index()
    {
        $idParticipant = Auth::user()->participant->id;

        // Récupère tous les groupes où le participant connecté apparaît dans la table pivot
        $groupes = Groupe::whereHas('participants', function($query) use ($idParticipant) {
            $query->where('id_participant', $idParticipant);
        })->with(['participants', 'course'])->get();

        return response()->json($groupes);
    }

    /**
     * Crée un nouveau groupe et y attache le créateur en tant que fondateur.
     * Pour les groupes de type "Entreprise", un code unique préfixé "E-" est généré automatiquement.
     * Si un groupe portant le même nom existe déjà pour la même course (cas challenge),
     * le participant est directement rattaché au groupe existant sans en créer un nouveau.
     * @author Ngoie Steven
     * @author Guillermet Jean-Daniel (logique de réutilisation du groupe existant pour le challenge)
     * @param  Request $request Doit contenir `nom`, `type` et optionnellement `id_course`.
     * @return \Illuminate\Http\JsonResponse Groupe créé (201) ou groupe existant réutilisé (200).
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nom'       => 'required|string|max:100',
            'type'      => 'required|string',
            'id_course' => 'nullable|exists:Course,id',
        ]);

        // Génère un code unique préfixé "E-" pour les groupes de type Entreprise
        if ($validatedData['type'] === 'Entreprise') {
            $prefixe = 'E-';
            // Boucle jusqu'à trouver un code qui n'existe pas encore en base
            do {
                $code = $prefixe . strtoupper(Str::random(7));
            } while (Groupe::where('code_entreprise', $code)->exists());

            $validatedData['code_entreprise'] = $code;
        } else {
            $validatedData['code_entreprise'] = null;
        }

        $idParticipant = Auth::user()->participant->id;

        // Cas challenge : si un groupe avec ce nom + cette course existe déjà,
        // on y attache le participant plutôt que de créer un doublon
        $groupeExistant = Groupe::where('nom', $validatedData['nom'])
            ->where('id_course', $validatedData['id_course'] ?? null)
            ->first();

        if ($groupeExistant) {
            // Attache le participant uniquement s'il n'est pas déjà membre du groupe
            if (!$groupeExistant->participants()->where('id_participant', $idParticipant)->exists()) {
                $groupeExistant->participants()->attach($idParticipant, [
                    'statut' => StatutParticipant::MEMBRE->value
                ]);
            }
            return response()->json($groupeExistant->load('participants'), 200);
        }

        $groupe = Groupe::create($validatedData);

        // Le créateur obtient automatiquement le statut de fondateur
        $groupe->participants()->attach($idParticipant, [
            'statut' => StatutParticipant::FONDATEUR->value
        ]);

        return response()->json($groupe->load('participants'), 201);
    }

    /**
     * Retourne le détail d'un groupe avec la liste de ses participants.
     * @author Ngoie Steven
     * @param  int $id Identifiant du groupe.
     * @return \Illuminate\Http\JsonResponse Groupe avec ses participants.
     */
    public function show($id)
    {
        $groupe = Groupe::with('participants')->findOrFail($id);
        return response()->json($groupe);
    }

    /**
     * Met à jour les informations d'un groupe (nom, code entreprise).
     * Réservé au fondateur du groupe.
     * @author Ngoie Steven
     * @param  Request $request Champs à mettre à jour.
     * @param  int     $id      Identifiant du groupe.
     * @return \Illuminate\Http\JsonResponse Groupe mis à jour ou 403 si non fondateur.
     */
    public function update(Request $request, $id)
    {
        $groupe        = Groupe::findOrFail($id);
        $idParticipant = Auth::user()->participant->id;

        // Seul le fondateur est autorisé à modifier le groupe
        $isFondateur = $groupe->participants()
            ->where('id_participant', $idParticipant)
            ->where('GroupeParticipant.statut', StatutParticipant::FONDATEUR->value)
            ->exists();

        if (!$isFondateur) {
            return response()->json(['message' => 'Non autorisé. Seul le fondateur peut modifier ce groupe.'], 403);
        }

        $validatedData = $request->validate([
            'nom'             => 'sometimes|required|string|max:100',
            'code_entreprise' => 'nullable|string|max:255',
        ]);

        $groupe->update($validatedData);

        return response()->json($groupe->load('participants'));
    }

    /**
     * Supprime un groupe et détache tous ses participants.
     * Réservé au fondateur du groupe.
     * @author Ngoie Steven
     * @param  int $id Identifiant du groupe à supprimer.
     * @return \Illuminate\Http\JsonResponse Message de confirmation ou 403 si non fondateur.
     */
    public function destroy($id)
    {
        $groupe        = Groupe::findOrFail($id);
        $idParticipant = Auth::user()->participant->id;

        $isFondateur = $groupe->participants()
            ->where('id_participant', $idParticipant)
            ->where('GroupeParticipant.statut', StatutParticipant::FONDATEUR->value)
            ->exists();

        if (!$isFondateur) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        // Détache tous les membres avant de supprimer pour éviter les orphelins en base pivot
        $groupe->participants()->detach();
        $groupe->delete();

        return response()->json(['message' => 'Groupe supprimé avec succès.']);
    }


    // ==========================================
    // GESTION DES MEMBRES
    // ==========================================

    /**
     * Ajoute un participant existant à un groupe.
     * Vérifie que les inscriptions sont encore ouvertes et que le groupe n'est pas complet.
     * Les profils rattachés directement au compte sont ajoutés comme membres immédiatement ;
     * les utilisateurs externes reçoivent le statut "En attente" (invitation à accepter).
     * @author Ngoie Steven
     * @author Guillermet Jean-Daniel (vérification inscriptions fermées et limite max_nb_personne)
     * @param  Request $request Doit contenir `id_participant`.
     * @param  int     $idGroupe Identifiant du groupe cible.
     * @return \Illuminate\Http\JsonResponse Groupe mis à jour ou erreur métier.
     */
    public function addParticipant(Request $request, $idGroupe)
    {
        $validatedData = $request->validate([
            'id_participant' => 'required|exists:Participant,id',
        ]);

        $groupe = Groupe::findOrFail($idGroupe);

        // Bloque l'ajout si les inscriptions pour la course associée sont fermées
        if ($groupe->id_course && !$groupe->course->isRegistrationOpen()) {
            return response()->json([
                'message' => 'Impossible d\'ajouter un membre, les inscriptions pour cette course sont fermées.'
            ], 403);
        }

        // Bloque si le groupe a atteint le nombre maximum de participants fixé par la course
        if ($groupe->id_course && $groupe->course->max_nb_personne) {
            $nbActuels = $groupe->participants()->count();
            if ($nbActuels >= $groupe->course->max_nb_personne) {
                return response()->json([
                    'message' => "Le groupe est complet. Cette course requiert exactement {$groupe->course->max_nb_personne} participant(s)."
                ], 422);
            }
        }

        // Retourne le groupe tel quel si le participant est déjà membre (idempotent)
        if ($groupe->participants()->where('id_participant', $validatedData['id_participant'])->exists()) {
            return response()->json([
                'message' => 'Ce participant est déjà dans le groupe.',
                'groupe'  => $groupe->load('participants'),
            ], 200);
        }

        // Détermine le statut selon que le participant est géré par le compte connecté ou non
        $participant        = Participant::find($validatedData['id_participant']);
        $estGereParLeCompte = $participant && $participant->id_user === Auth::id();

        // Sous-profil du compte → membre direct ; utilisateur externe → invitation en attente
        $statut = $estGereParLeCompte ? 'Membre' : StatutParticipant::EN_ATTENTE->value;

        $groupe->participants()->attach($validatedData['id_participant'], [
            'statut' => $statut
        ]);

        return response()->json([
            'message' => $estGereParLeCompte
                ? 'Participant ajouté en tant que membre directement.'
                : 'Invitation envoyée (Participant ajouté en attente).',
            'groupe'  => $groupe->load('participants')
        ]);
    }

    /**
     * Retire un participant d'un groupe.
     * @author Ngoie Steven
     * @param  int $idGroupe      Identifiant du groupe.
     * @param  int $idParticipant Identifiant du participant à retirer.
     * @return \Illuminate\Http\JsonResponse Groupe mis à jour.
     */
    public function removeParticipant($idGroupe, $idParticipant)
    {
        $groupe = Groupe::findOrFail($idGroupe);

        $groupe->participants()->detach($idParticipant);

        return response()->json([
            'message' => 'Participant retiré du groupe.',
            'groupe'  => $groupe->load('participants')
        ]);
    }

    // ==========================================
    // VALIDATION CODE ENTREPRISE (panier)
    // ==========================================

    /**
     * Vérifie qu'un code entreprise est valide, que le participant appartient bien au groupe
     * correspondant et que les inscriptions sont encore ouvertes.
     * Utilisé lors de la validation du panier pour les courses de type Entreprise.
     * @author Ngoie Steven
     * @author Guillermet Jean-Daniel (récupération idParticipant, vérification appartenance groupe, message)
     * @param  Request $request Doit contenir `code`.
     * @return \Illuminate\Http\JsonResponse `{valide, message, groupe}` ou erreur 404/403.
     */
    public function verifierCodeEntreprise(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $idParticipant = Auth::user()->participant->id;

        // Cherche le groupe associé à ce code entreprise
        $groupe = Groupe::where('code_entreprise', $request->code)->first();

        if (!$groupe) {
            return response()->json([
                'valide'  => false,
                'message' => 'Ce code de participation est invalide.'
            ], 404);
        }

        // Vérifie que le participant connecté fait bien partie de ce groupe
        $estMembre = $groupe->participants()->where('id_participant', $idParticipant)->exists();

        if (!$estMembre) {
            return response()->json([
                'valide'  => false,
                'message' => 'Vous ne faites pas partie du groupe associé à ce code.'
            ], 403);
        }

        // Vérifie que les inscriptions sont encore ouvertes pour cette course
        if ($groupe->id_course && !$groupe->course->isRegistrationOpen()) {
            return response()->json([
                'message' => 'Le code est correct, mais les inscriptions pour cette course entreprise sont désormais fermées.'
            ], 403);
        }

        // Retourne les informations minimales du groupe au frontend pour valider le panier
        return response()->json([
            'valide'  => true,
            'message' => 'Code appliqué avec succès !',
            'groupe'  => $groupe->only(['id', 'nom', 'type'])
        ], 200);
    }

    // ==========================================
    // GESTION DES INVITATIONS
    // ==========================================

    /**
     * Retourne les groupes pour lesquels le participant connecté a une invitation en attente.
     * Chaque groupe est enrichi du questionnaire formaté de sa course si applicable,
     * afin que le participant puisse répondre au questionnaire lors de l'acceptation.
     * Un tag "Invitation à un groupe" est ajouté pour l'affichage dans le tableau de bord.
     * @author Ngoie Steven
     * @author Neris Alessandro (chargement des questions/choix, formatage du questionnaire)
     * @return \Illuminate\Http\JsonResponse Liste des groupes avec invitation en attente.
     */
    public function getInvitations()
    {
        $idParticipant = Auth::user()->participant->id;

        $invitations = Groupe::whereIn('id', function($query) use ($idParticipant) {
            $query->select('id_groupe')
                  ->from('GroupeParticipant') // Cible explicitement la table pivot
                  ->where('id_participant', $idParticipant)
                  ->where('statut', StatutParticipant::EN_ATTENTE->value);
        })
        ->with('participants', 'course.questions.choix', 'course.evenement')
        ->get()
        ->map(function($groupe) {
            $course = $groupe->course;

            // Formate le questionnaire en structure normalisée {id, question, answers[]}
            // uniquement si la course en possède un
            if ($course && $course->is_questionnaire && $course->questions) {
                $course->questionnaire = $course->questions->map(function($q) {
                    return [
                        'id'       => $q->id,
                        'question' => $q->enonce,
                        'answers'  => $q->choix->map(function($choix) {
                            return [
                                'id'    => $choix->id,
                                'texte' => $choix->texte_option,
                            ];
                        }),
                    ];
                });
            } else {
                $course->questionnaire = null;
            }

            return $groupe;
        });

        // Ajoute un tag d'affichage pour différencier ce type de notification dans le tableau de bord
        $invitations->each->setAttribute('tag', 'Invitation à un groupe');

        return response()->json($invitations);
    }

    /**
     * Accepte une invitation à rejoindre un groupe.
     * Passe le statut du participant de "En attente" à "Membre",
     * valide les inscriptions de tous les membres du groupe
     * et enregistre les réponses au questionnaire si la course en possède un.
     * L'ensemble est enveloppé dans une transaction pour garantir la cohérence.
     * @author Neris Alessandro
     * @param  Request $request Peut contenir `reponses` (tableau de réponses au questionnaire).
     * @param  int     $idGroupe Identifiant du groupe dont l'invitation est acceptée.
     * @return \Illuminate\Http\JsonResponse Confirmation (200) ou 403 si inscriptions fermées.
     */
    public function accepterInvitation(Request $request, $idGroupe)
    {
        $idParticipant = Auth::user()->participant->id;
        $groupe        = Groupe::findOrFail($idGroupe);

        // Bloque si les inscriptions pour cette course sont désormais fermées
        if ($groupe->id_course && !$groupe->course->isRegistrationOpen()) {
            return response()->json([
                'message' => 'Cette invitation a expiré car les inscriptions pour cette course sont clôturées.'
            ], 403);
        }

        \DB::beginTransaction();
        try {
            // Passe le statut du participant de "En attente" à "Membre" dans la table pivot
            $groupe->participants()->updateExistingPivot($idParticipant, [
                'statut' => 'Membre'
            ]);

            // Valide les inscriptions de tous les membres du groupe (fondateur inclus)
            \App\Models\Inscription::where('id_groupe', $idGroupe)
                ->update(['status_paiement' => 'Validé']);

            // Enregistre les réponses au questionnaire si fournies
            $reponses = $request->input('reponses', []);
            if (!empty($reponses)) {
                $inscription = \App\Models\Inscription::where('id_groupe', $idGroupe)
                    ->where('id_participant', $idParticipant)
                    ->first();

                if ($inscription) {
                    foreach ($reponses as $data) {
                        \App\Models\ReponseQuestion::updateOrCreate(
                            [
                                'id_inscription' => $inscription->id,
                                'id_question'    => $data['id_question'] ?? null,
                            ],
                            [
                                'id_option_choisie' => $data['id_option_choisie'] ?? null,
                            ]
                        );
                    }
                }
            }

            \DB::commit();

            return response()->json([
                'message' => 'Invitation acceptée avec succès.',
                'groupe'  => $groupe
            ], 200);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de l\'acceptation : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refuse une invitation à rejoindre un groupe.
     * Détache le participant du groupe, annule son inscription pour cette course
     * et notifie le fondateur par message interne et par email (non-bloquant).
     * @author Ngoie Steven
     * @param  int $idGroupe Identifiant du groupe dont l'invitation est refusée.
     * @return \Illuminate\Http\JsonResponse Message de confirmation.
     */
    public function refuserInvitation($idGroupe)
    {
        $participantConnecte = Auth::user()->participant;
        $idParticipant       = $participantConnecte->id;
        $groupe              = Groupe::findOrFail($idGroupe);

        // Retire le participant de la table pivot GroupeParticipant
        $groupe->participants()->detach($idParticipant);

        // Annule l'inscription liée à ce groupe pour ce participant
        \App\Models\Inscription::where('id_participant', $idParticipant)
            ->where('id_groupe', $idGroupe)
            ->update(['status_paiement' => 'Annulé']);

        // Notifie le fondateur du groupe du refus
        $fondateur = $groupe->participants()->wherePivot('statut', 'fondateur')->first();

        if ($fondateur && $fondateur->user) {
            // Notification interne via le système de messages
            Message::create([
                'content' => json_encode([
                    'recipient_user_id' => $fondateur->user->id,
                    'sender_user_id'    => $participantConnecte->user?->id,
                    'type'              => 'group_invitation_refused',
                    'groupe_id'         => $groupe->id,
                ], JSON_UNESCAPED_UNICODE),
            ]);

            // Email de notification au fondateur (non-bloquant, loguée en local via MAIL_MAILER=log)
            try {
                \Illuminate\Support\Facades\Mail::send('emails.invitation_refusee', [
                    'fondateur' => $fondateur,
                    'invite'    => $participantConnecte,
                    'groupe'    => $groupe
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