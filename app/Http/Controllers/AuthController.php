<?php

/**
 * @fileoverview AuthController.php
 * @description Contrôleur d'authentification et de gestion des comptes.
 *              Gère l'inscription, la connexion, la déconnexion, la modification
 *              du mot de passe, la gestion des sous-profils participants,
 *              la recherche d'utilisateurs et le système de notifications.
 * @author Guillermet Jean-Daniel
 */

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Participant;
use App\Models\Groupe;
use App\Models\Inscription;
use App\Models\FormulaireMembership;
use App\Models\InvitationMembership;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Mail\InvitationParticipantMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Inscrit un nouvel utilisateur et crée son profil participant associé.
     * Le mot de passe doit contenir au moins une majuscule, une minuscule,
     * un chiffre et un caractère spécial. La photo est stockée en BLOB si fournie.
     * Un token Sanctum est retourné directement après l'inscription.
     * @author Guillermet Jean-Daniel
     * @author MurasameMk5 (règle de validation Rule::date pour date_naissance)
     * @param  Request $request Données du formulaire d'inscription complet.
     * @return \Illuminate\Http\JsonResponse Token d'authentification et données utilisateur (201).
     */
    public function register(Request $request)
    {
        $request->validate([
            'email'          => 'required|email|max:80|unique:User,email',
            // Le mot de passe doit contenir majuscule, minuscule, chiffre et caractère spécial
            'password'       => ['required', 'confirmed', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/'],
            'nom'            => 'required|string|max:100',
            'prenom'         => 'required|string|max:100',
            'date_naissance' => ['required', 'date', Rule::date()->beforeOrEqual(today())],
            'telephone'      => 'required|string|max:20|unique:Participant,telephone',
            'nationalite'    => 'required|string|max:100',
            'adresse'        => 'required|string|max:100',
            'code_postal'    => 'required|string|max:10',
            'ville'          => 'required|string|max:100',
            'pays'           => 'required|string|max:100',
            'taille_tshirt'  => 'required|string|max:10',
            'sexe'           => 'required|string|max:10',
            'equipe_nom'     => 'nullable|string|max:100',
            'instagram'      => 'nullable|string|max:255',
            'facebook'       => 'nullable|string|max:255',
            'photo'          => 'nullable|image|max:2048',
        ], [
            'date_naissance.before_or_equal' => 'La date de naissance ne peut pas être dans le futur.',
        ]);

        $user = User::create([
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Convertit la photo en BLOB si fournie, null sinon
        $photo = null;
        if ($request->hasFile('photo')) {
            $photo = file_get_contents($request->file('photo')->getRealPath());
        }

        Participant::create([
            'id_user'        => $user->id,
            'nom'            => $request->nom,
            'prenom'         => $request->prenom,
            'date_naissance' => $request->date_naissance,
            'telephone'      => $request->telephone,
            'nationalite'    => $request->nationalite,
            'adresse'        => $request->adresse,
            'code_postal'    => $request->code_postal,
            'ville'          => $request->ville,
            'pays'           => $request->pays,
            'taille_tshirt'  => $request->taille_tshirt,
            'sexe'           => $request->sexe,
            'equipe_nom'     => $request->equipe_nom,
            'instagram'      => $request->instagram,
            'facebook'       => $request->facebook,
            'photo'          => $photo,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->buildUserResponse($user),
        ], 201);
    }

    /**
     * Authentifie un utilisateur et retourne un token Sanctum.
     * Lance une ValidationException si les identifiants sont incorrects.
     * @author Guillermet Jean-Daniel
     * @param  Request $request Doit contenir `email` et `password`.
     * @return \Illuminate\Http\JsonResponse Token d'authentification et données utilisateur.
     * @throws ValidationException Si les identifiants ne correspondent à aucun compte.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => 'Les identifiants sont incorrects.',
            ]);
        }

        $user  = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->buildUserResponse($user),
        ]);
    }

    /**
     * Révoque le token d'accès courant et déconnecte l'utilisateur.
     * @author Guillermet Jean-Daniel
     * @param  Request $request Requête authentifiée.
     * @return \Illuminate\Http\JsonResponse Message de confirmation.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie.'
        ]);
    }

    /**
     * Retourne les données de l'utilisateur actuellement connecté.
     * @author Guillermet Jean-Daniel
     * @param  Request $request Requête authentifiée.
     * @return \Illuminate\Http\JsonResponse Utilisateur avec son profil participant et ses rôles.
     */
    public function me(Request $request)
    {
        return response()->json($this->buildUserResponse($request->user()));
    }

    /**
     * Met à jour le mot de passe de l'utilisateur connecté.
     * Vérifie que le mot de passe actuel est correct avant d'appliquer la modification.
     * @author Guillermet Jean-Daniel
     * @param  Request $request Doit contenir `currentPassword`, `newPassword` et `newPassword_confirmation`.
     * @return \Illuminate\Http\JsonResponse Message de confirmation ou 422 si le mot de passe actuel est incorrect.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'currentPassword' => 'required|string',
            'newPassword'     => 'required|string|min:8|confirmed',
        ], [
            'newPassword.confirmed' => 'La confirmation du nouveau mot de passe ne correspond pas.',
        ]);

        $user = $request->user();

        // Vérifie que le mot de passe actuel fourni correspond bien à celui stocké en base
        if (!Hash::check($validated['currentPassword'], $user->password)) {
            return response()->json([
                'message' => 'Le mot de passe actuel est incorrect.',
                'errors'  => ['currentPassword' => ['Le mot de passe actuel est incorrect.']],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['newPassword']),
        ]);

        return response()->json(['message' => 'Mot de passe modifié avec succès.']);
    }

    /**
     * Construit la réponse utilisateur standardisée avec ses relations.
     * Utilisée par register, login et me pour garantir une structure de réponse cohérente.
     * @author Guillermet Jean-Daniel
     * @param  User $user Utilisateur à sérialiser.
     * @return User Utilisateur avec ses relations participant et roles chargées.
     */
    private function buildUserResponse(User $user): User
    {
        return $user->load('participant', 'roles');
    }

    /**
     * Recherche un participant existant par adresse email.
     * Utilisé pour inviter un participant déjà inscrit dans un groupe sans exposer ses données sensibles.
     * @author Guillermet Jean-Daniel
     * @param  Request $request Doit contenir `email`.
     * @return \Illuminate\Http\JsonResponse Données publiques du participant (id, prenom, nom, email) ou 404.
     */
    public function rechercherParticipant(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)
            ->with('participant')
            ->first();

        if (!$user || !$user->participant) {
            return response()->json([
                'message' => 'Aucun participant trouvé avec cette adresse email.'
            ], 404);
        }

        // Expose uniquement les champs non sensibles
        $participant = $user->participant;
        return response()->json([
            'id'     => $participant->id,
            'prenom' => $participant->prenom,
            'nom'    => $participant->nom,
            'email'  => $user->email,
        ]);
    }

    /**
     * Crée un compte utilisateur par invitation (sans mot de passe défini par l'utilisateur).
     * Un mot de passe aléatoire sécurisé est généré et envoyé par email afin que
     * l'invité puisse se connecter et définir ses propres identifiants.
     * L'envoi du mail est non-bloquant : une erreur d'envoi est loguée sans interrompre la création.
     * @author Ngoie Steven
     * @param  Request $request Données complètes du participant invité (email requis).
     * @return \Illuminate\Http\JsonResponse Données publiques du participant créé (201).
     */
    public function createInvitedUser(Request $request)
    {
        // Validation sans champ password : le mot de passe est généré automatiquement
        $request->validate([
            'email'          => 'required|email|max:80|unique:User,email',
            'nom'            => 'required|string|max:100',
            'prenom'         => 'required|string|max:100',
            'date_naissance' => 'required|date',
            'telephone'      => 'required|string|max:20|unique:Participant,telephone',
            'nationalite'    => 'nullable|string|max:100', // nullable : non demandé dans le formulaire frontend
            'adresse'        => 'required|string|max:100',
            'code_postal'    => 'required|string|max:10',
            'ville'          => 'required|string|max:100',
            'pays'           => 'required|string|max:100',
            'taille_tshirt'  => 'required|string|max:10',
            'sexe'           => 'required|string|max:10',
        ]);

        // Génère un mot de passe aléatoire sécurisé de 16 caractères (majuscule, minuscule, chiffre, spécial)
        $randomPassword = \Illuminate\Support\Str::password(16, true, true, true, false);

        $user = User::create([
            'email'    => $request->email,
            'password' => Hash::make($randomPassword),
        ]);

        $participant = Participant::create([
            'id_user'        => $user->id,
            'nom'            => $request->nom,
            'prenom'         => $request->prenom,
            'date_naissance' => $request->date_naissance,
            'telephone'      => $request->telephone,
            // Nationalité initialisée au pays par défaut ; le participant la précisera lors de sa première connexion
            'nationalite'    => $request->nationalite ?? $request->pays,
            'adresse'        => $request->adresse,
            'code_postal'    => $request->code_postal,
            'ville'          => $request->ville,
            'pays'           => $request->pays,
            'taille_tshirt'  => $request->taille_tshirt,
            'sexe'           => $request->sexe,
        ]);

        // Envoi de l'email d'invitation avec le mot de passe temporaire (non-bloquant)
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(
                new \App\Mail\InvitationParticipantMail($participant->load('user'), $randomPassword)
            );
        } catch (\Exception $e) {
            // L'échec d'envoi ne bloque pas la création du compte ; l'erreur est loguée pour le débogage
            \Illuminate\Support\Facades\Log::error("Erreur d'envoi d'email d'invitation : " . $e->getMessage());
        }

        return response()->json([
            'message'     => 'Utilisateur invité créé avec succès.',
            'participant' => [
                'id'     => $participant->id,
                'prenom' => $participant->prenom,
                'nom'    => $participant->nom,
                'email'  => $user->email,
            ]
        ], 201);
    }

    /**
     * Retourne tous les profils participants rattachés au compte connecté.
     * Inclut le profil principal et tous les sous-profils créés par l'utilisateur.
     * La photo est exclue de la sélection pour alléger la réponse.
     * @author Guillermet Jean-Daniel
     * @author Ngoie Steven (sélection explicite des colonnes sans photo)
     * @param  Request $request Requête authentifiée.
     * @return \Illuminate\Http\JsonResponse Liste des participants du compte.
     */
    public function mesParticipants(Request $request)
    {
        $user = $request->user();
        $participants = Participant::where('id_user', $user->id)
            ->select([
                'id',
                'id_user',
                'nom',
                'prenom',
                'date_naissance',
                'equipe_nom',
                'adresse',
                'code_postal',
                'ville',
                'pays',
                'telephone',
                'nationalite',
                'instagram',
                'facebook',
                'taille_tshirt',
                'sexe',
            ])
            ->get();

        return response()->json($participants);
    }

    /**
     * Crée un nouveau sous-profil participant rattaché au compte connecté.
     * Si un email est fourni, un compte indépendant est créé pour ce participant
     * avec un mot de passe aléatoire envoyé par email.
     * Sinon, le participant est simplement ajouté comme sous-profil sans compte propre.
     * La date de naissance est convertie du format d/m/Y (frontend) vers Y-m-d (MySQL).
     * @author Ngoie Steven
     * @param  Request $request Données du participant. L'`email` est optionnel.
     * @return \Illuminate\Http\JsonResponse Participant créé (201).
     */
    public function creerParticipant(Request $request)
    {
        $request->validate([
            'nom'            => 'required|string|max:100',
            'prenom'         => 'required|string|max:100',
            'date_naissance' => 'nullable|string|max:20',
            'adresse'        => 'nullable|string|max:100',
            'code_postal'    => 'nullable|string|max:10',
            'ville'          => 'nullable|string|max:100',
            'pays'           => 'nullable|string|max:100',
            'telephone'      => 'nullable|string|max:20|unique:Participant,telephone',
            // Si fourni, l'email doit être unique dans la table User (crée un compte indépendant)
            'email'          => 'nullable|email|max:80|unique:User,email',
            'taille_tshirt'  => 'nullable|string|max:10',
            'sexe'           => 'nullable|string|max:10',
        ]);

        $idUserRattachement = $request->user()->id; // Par défaut : sous-profil du compte connecté
        $nouveauUser        = null;
        $randomPassword     = null;

        // Si un email est fourni, on crée un compte indépendant avec mot de passe aléatoire
        if ($request->filled('email')) {
            $randomPassword = \Illuminate\Support\Str::password(16, true, true, true, false);

            $nouveauUser = User::create([
                'email'    => $request->email,
                'password' => Hash::make($randomPassword),
            ]);

            // Le participant sera rattaché au nouveau compte plutôt qu'au compte connecté
            $idUserRattachement = $nouveauUser->id;
        }

        // Conversion de la date de naissance : le frontend envoie d/m/Y, MySQL attend Y-m-d
        $dateNaissance = $request->date_naissance;
        if ($dateNaissance && str_contains($dateNaissance, '/')) {
            $dateNaissance = \Carbon\Carbon::createFromFormat('d/m/Y', $dateNaissance)->format('Y-m-d');
        }

        $participant = Participant::create([
            'id_user'        => $idUserRattachement,
            'nom'            => $request->nom,
            'prenom'         => $request->prenom,
            'date_naissance' => $dateNaissance,
            'adresse'        => $request->adresse ?? '',
            'code_postal'    => $request->code_postal ?? '',
            'ville'          => $request->ville ?? '',
            'pays'           => $request->pays ?? 'Suisse',
            'telephone'      => $request->telephone ?? null,
            'email'          => $request->email ?? null,
            'taille_tshirt'  => $request->taille_tshirt ?? 'M',
            'sexe'           => $request->sexe ?? 'M',
            'nationalite'    => 'Suisse',
        ]);

        // Envoie le mail d'invitation uniquement si un nouveau compte indépendant a été créé
        if ($nouveauUser) {
            try {
                \Illuminate\Support\Facades\Mail::to($nouveauUser->email)->send(
                    new \App\Mail\InvitationParticipantMail($participant->load('user'), $randomPassword)
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Erreur d'envoi d'email d'invitation au nouveau compte : " . $e->getMessage());
            }
        }

        return response()->json($participant, 201);
    }

    /**
     * Met à jour un sous-profil participant appartenant au compte connecté.
     * Interdit explicitement la modification du profil principal via cette route
     * (celui-ci doit être modifié via la page profil dédiée).
     * @author Guillermet Jean-Daniel
     * @param  Request $request Champs à mettre à jour.
     * @param  int     $id      Identifiant du participant à modifier.
     * @return \Illuminate\Http\JsonResponse Participant mis à jour (200) ou 403 si accès non autorisé.
     */
    public function majParticipant(Request $request, $id)
    {
        $user = $request->user();
        $participant = Participant::where('id', $id)
            ->where('id_user', $user->id)
            ->firstOrFail();

        // Le profil principal ne peut pas être modifié ici : renvoyer vers la page profil
        if ($participant->id === $user->participant?->id) {
            return response()->json(['message' => 'Utilisez la page profil pour modifier votre profil principal.'], 403);
        }

        $request->validate([
            'nom'            => 'required|string|max:100',
            'prenom'         => 'required|string|max:100',
            'date_naissance' => 'nullable|string|max:20',
            // Ignore l'unicité pour l'enregistrement courant afin d'éviter un faux conflit
            'telephone'      => 'nullable|string|max:20|unique:Participant,telephone,' . $participant->id,
            'taille_tshirt'  => 'nullable|string|max:10',
            'sexe'           => 'nullable|string|max:10',
            'nationalite'    => 'nullable|string|max:100',
        ]);

        $participant->update([
            'nom'            => $request->nom,
            'prenom'         => $request->prenom,
            'date_naissance' => $request->date_naissance,
            'telephone'      => $request->telephone ?? null,
            'taille_tshirt'  => $request->taille_tshirt ?? $participant->taille_tshirt,
            'sexe'           => $request->sexe ?? $participant->sexe,
            'nationalite'    => $request->nationalite ?? $participant->nationalite,
        ]);

        return response()->json($participant, 200);
    }

    /**
     * Supprime un sous-profil participant appartenant au compte connecté.
     * Interdit la suppression du profil principal et bloque si des inscriptions actives existent.
     * @author Guillermet Jean-Daniel
     * @param  Request $request Requête authentifiée.
     * @param  int     $id      Identifiant du participant à supprimer.
     * @return \Illuminate\Http\JsonResponse Message de confirmation (200), 403 ou 422 selon le cas.
     */
    public function supprimerParticipant(Request $request, $id)
    {
        $user = $request->user();
        $participant = Participant::where('id', $id)
            ->where('id_user', $user->id)
            ->firstOrFail();

        // Le profil principal du compte ne peut pas être supprimé
        if ($participant->id === $user->participant?->id) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre profil principal.'], 403);
        }

        // Bloque la suppression si le participant a des inscriptions actives ou en attente
        $inscriptionsActives = \App\Models\Inscription::where('id_participant', $participant->id)
            ->whereIn('status_paiement', ['Validé', 'En attente'])
            ->count();

        if ($inscriptionsActives > 0) {
            return response()->json([
                'message' => "Ce participant a {$inscriptionsActives} inscription(s) active(s). Annulez-les avant de supprimer ce profil."
            ], 422);
        }

        $participant->delete();

        return response()->json(['message' => 'Participant supprimé avec succès.'], 200);
    }

    /**
     * Retourne les notifications de l'utilisateur connecté sous forme enrichie.
     * Les notifications sont stockées en base sous forme de messages JSON génériques.
     * Cette méthode filtre, transforme et enrichit chaque message avec un titre
     * et un texte lisible selon son type (échange refusé, invitation groupe, membership...).
     * Les invitations membership déjà traitées (status != 'En cours') sont automatiquement exclues.
     * @author Ngoie Steven
     * @param  Request $request Requête authentifiée.
     * @return \Illuminate\Http\JsonResponse Liste des notifications enrichies de l'utilisateur.
     */
    public function mesNotificationsInfo(Request $request)
    {
        $messages = Message::orderByDesc('id')->get()->map(function (Message $message) use ($request) {
            $payload = json_decode($message->content, true);

            // Filtre : ne conserve que les messages destinés à l'utilisateur connecté
            if (!is_array($payload) || ($payload['recipient_user_id'] ?? null) !== $request->user()->id) {
                return null;
            }

            // Masque les invitations membership déjà acceptées ou refusées
            if (($payload['type'] ?? null) === 'membership_invitation_to_complete') {
                $invitationId = $payload['invitation_id'] ?? null;
                $invitation = $invitationId ? InvitationMembership::find($invitationId) : null;

                if (!$invitation || $invitation->status !== 'En cours') {
                    return null;
                }
            }

            // Associe un titre lisible à chaque type de notification
            $payload['title'] = match ($payload['type'] ?? null) {
                'exchange_refused'                            => 'Demande échange dossard refusée',
                'group_invitation_refused'                    => 'Invitation à un groupe refusée',
                'new_membership_request'                      => 'Membership complété',
                'membership_invitation_to_complete'           => 'Invitation membership',
                'membership_invitation_info'                  => 'Invitation membership envoyée',
                'membership_invitation_cancelled_info'        => 'Invitation membership annulée',
                'membership_invitation_cancelled_participant' => 'Invitation membership annulée',
                'membership_refused_info'                     => 'Membership refusé',
                default                                       => 'Information',
            };

            $membershipDecisionMaker = $this->resolveMembershipDecisionMakerLabel($payload);
            $membershipAdminActor    = $this->resolveMembershipAdminActorLabel($payload);

            // Construit le texte du contenu selon le type de notification
            $payload['content'] = match ($payload['type'] ?? null) {
                'exchange_refused'           => $this->buildExchangeRefusedNotification($payload),
                'group_invitation_refused'   => $this->buildGroupRefusedNotification($payload),
                'new_membership_request'     => sprintf(
                    '%s %s a complété sa demande membership.',
                    $payload['prenom'] ?? 'Quelqu\'un',
                    $payload['nom'] ?? ''
                ),
                'membership_invitation_to_complete' => sprintf(
                    '%s vous a invité à compléter votre formulaire membership.',
                    $membershipAdminActor
                ),
                'membership_invitation_info' => sprintf(
                    '%s a invité %s %s à compléter son formulaire membership.',
                    $membershipAdminActor,
                    $payload['participant_prenom'] ?? 'Un participant',
                    $payload['participant_nom'] ?? ''
                ),
                'membership_invitation_cancelled_info' => sprintf(
                    '%s a annulé l\'invitation membership de %s %s.',
                    $membershipAdminActor,
                    $payload['participant_prenom'] ?? 'Un participant',
                    $payload['participant_nom'] ?? ''
                ),
                'membership_invitation_cancelled_participant' => sprintf(
                    '%s a annulé votre invitation membership.',
                    $membershipAdminActor
                ),
                'membership_refused_info' => sprintf(
                    '%s a refusé la demande de membership de %s %s.',
                    $membershipDecisionMaker,
                    $payload['prenom'] ?? 'Quelqu\'un',
                    $payload['nom'] ?? ''
                ),
                default => 'Notification.',
            };

            $payload['id']         = $message->id;
            $payload['created_at'] = $message->created_at;

            return $payload;
        })->filter()->values();

        return response()->json($messages);
    }

    /**
     * Supprime une notification de l'utilisateur connecté.
     * Vérifie que la notification appartient bien à l'utilisateur avant de la supprimer.
     * @author Ngoie Steven
     * @param  Request $request Requête authentifiée.
     * @param  int     $id      Identifiant du message à supprimer.
     * @return \Illuminate\Http\JsonResponse Message de confirmation ou 403 si non autorisé.
     */
    public function supprimerNotificationInfo(Request $request, int $id)
    {
        $message = Message::findOrFail($id);
        $payload = json_decode($message->content, true);

        // Vérifie que le message est bien destiné à l'utilisateur connecté
        if (!is_array($payload) || ($payload['recipient_user_id'] ?? null) !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $message->delete();

        return response()->json(['message' => 'Notification supprimée avec succès.']);
    }

    /**
     * Construit le texte de notification pour un refus d'échange de dossard.
     * Charge l'expéditeur et l'inscription concernée pour personnaliser le message.
     * @author Ngoie Steven
     * @param  array $payload Payload JSON du message de notification.
     * @return string Texte lisible décrivant le refus.
     */
    private function buildExchangeRefusedNotification(array $payload): string
    {
        $sender      = User::with('participant')->find($payload['sender_user_id'] ?? null);
        $inscription = Inscription::with(['course.evenement', 'course', 'dossard'])->find($payload['inscription_a_id'] ?? null);

        $prenom    = $sender?->participant?->prenom ?? 'Quelqu\'un';
        $nom       = $sender?->participant?->nom ?? '';
        $evenement = $inscription?->course?->evenement?->nom ?? 'l\'évènement';
        $course    = $inscription?->course?->nom ?? 'la course';

        return sprintf('%s %s a refusé votre échange de dossard pour %s - %s.', $prenom, $nom, $evenement, $course);
    }

    /**
     * Construit le texte de notification pour un refus d'invitation à un groupe.
     * @author Ngoie Steven
     * @param  array $payload Payload JSON du message de notification.
     * @return string Texte lisible décrivant le refus.
     */
    private function buildGroupRefusedNotification(array $payload): string
    {
        $sender = User::with('participant')->find($payload['sender_user_id'] ?? null);
        $groupe = Groupe::find($payload['groupe_id'] ?? null);

        $prenom = $sender?->participant?->prenom ?? 'Quelqu\'un';
        $nom    = $sender?->participant?->nom ?? '';

        return sprintf('%s %s a refusé votre invitation au groupe %s.', $prenom, $nom, $groupe?->nom ?? '—');
    }

    /**
     * Résout le nom lisible de l'administrateur ayant pris une décision sur un membership.
     * Cherche d'abord dans le payload, puis en base de données via l'email si le nom n'est pas présent.
     * Retourne "Un autre admin" si aucune information n'est disponible.
     * @author Ngoie Steven
     * @param  array $payload Payload JSON contenant les champs `admin_decideur_*`.
     * @return string Nom complet de l'administrateur décisionnaire ou libellé générique.
     */
    private function resolveMembershipDecisionMakerLabel(array $payload): string
    {
        $prenom   = trim((string) ($payload['admin_decideur_prenom'] ?? ''));
        $nom      = trim((string) ($payload['admin_decideur_nom'] ?? ''));
        $fullName = trim($prenom . ' ' . $nom);

        if ($fullName !== '') {
            return $fullName;
        }

        // Fallback : recherche en base via l'email si le nom n'est pas dans le payload
        $email = $payload['admin_decideur_email'] ?? null;
        if (is_string($email) && $email !== '') {
            $admin  = User::with('participant')->where('email', $email)->first();
            $dbName = trim(trim((string) ($admin?->participant?->prenom ?? '')) . ' ' . trim((string) ($admin?->participant?->nom ?? '')));

            if ($dbName !== '') {
                return $dbName;
            }
        }

        return 'Un autre admin';
    }

    /**
     * Résout le nom lisible de l'administrateur ayant effectué une action sur une invitation membership.
     * Contrairement à resolveMembershipDecisionMakerLabel, cherche uniquement dans le payload (pas en base).
     * Retourne "Un autre admin" si aucune information n'est disponible.
     * @author Ngoie Steven
     * @param  array $payload Payload JSON contenant les champs `admin_prenom` et `admin_nom`.
     * @return string Nom complet de l'administrateur acteur ou libellé générique.
     */
    private function resolveMembershipAdminActorLabel(array $payload): string
    {
        $prenom   = trim((string) ($payload['admin_prenom'] ?? ''));
        $nom      = trim((string) ($payload['admin_nom'] ?? ''));
        $fullName = trim($prenom . ' ' . $nom);

        if ($fullName !== '') {
            return $fullName;
        }

        return 'Un autre admin';
    }
}