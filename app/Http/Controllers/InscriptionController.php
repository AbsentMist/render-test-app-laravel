<?php

/**
 * @fileoverview InscriptionController.php
 * @description Contrôleur REST gérant le cycle de vie complet des inscriptions aux courses :
 *              création, lecture, modification, annulation et export.
 *              Deux niveaux d'accès sont supportés : Administrateur et Participant.
 * @author Ngoie Steven
 */

namespace App\Http\Controllers;

use App\Models\Inscription;
use App\Models\Course;
use App\Models\Dossard;
use App\Models\ChoixOption;
use App\Models\FormulaireMembership;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Models\Participant;
use App\Models\Groupe;
use App\Models\CodeRabais;
use App\Exports\InscriptionsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Mail\ConfirmationInscriptionMail;
use Illuminate\Support\Facades\Mail;

class InscriptionController extends Controller
{
    /**
     * Retourne toutes les inscriptions avec leurs relations pour la vue administrateur.
     * La photo du participant est exclue pour alléger la réponse JSON.
     * @author Ngoie Steven
     * @return \Illuminate\Http\JsonResponse Liste complète des inscriptions triées par date de paiement décroissante.
     */
    public function indexAdmin()
    {
        // Vérifie que l'utilisateur connecté possède le rôle Administrateur
        $user = Auth::user();
        if (!$user->roles()->where('type', 'Administrateur')->exists()) {
            return response()->json(['message' => 'Accès non autorisé. Réservé aux administrateurs.'], 403);
        }

        // Colonnes sélectionnées pour le participant : on exclut la photo pour alléger la réponse
        $participantColumns = [
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
        ];

        $inscriptions = Inscription::with([
            'course.evenement',
            'participant' => fn ($query) => $query->select($participantColumns),
            'participant.user' => fn ($query) => $query->select('id', 'email'),
            'dossard',
            'groupe',
            'choixOptions.option',
            'reponsesQuestions.question',
            'reponsesQuestions.option',
            'documentsFournis',
            'ancienneInscription.course',
            'ancienneInscription.participant' => fn ($query) => $query->select($participantColumns),
            'ancienneInscription.groupe',
        ])->orderBy('date_paiement', 'desc')->get();

        return response()->json($inscriptions);
    }

    /**
     * Retourne toutes les inscriptions du participant connecté.
     * @author Ngoie Steven
     * @return \Illuminate\Http\JsonResponse Inscriptions filtrées par l'identifiant du participant connecté.
     */
    public function indexParticipant()
    {
        $user = Auth::user();

        // Récupération de l'ID du participant rattaché au compte connecté
        $idParticipant = $user->participant->id;

        // Colonnes sélectionnées pour le participant : on exclut la photo pour alléger la réponse
        $participantColumns = [
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
        ];

        $inscriptions = Inscription::with([
            'course.evenement',
            'dossard',
            'groupe',
            'participant' => fn ($query) => $query->select($participantColumns),
            'choixOptions.option',
            'reponsesQuestions.question',
            'documentsFournis',
            'ancienneInscription.course',
            'ancienneInscription.participant' => fn ($query) => $query->select($participantColumns),
            'ancienneInscription.groupe',
        ])
            ->where('id_participant', $idParticipant)
            ->get();

        return response()->json($inscriptions);
    }

    /**
     * Crée une nouvelle inscription pour un participant.
     * Gère les cas suivants : nouvelle inscription, réinscription après annulation,
     * inscription individuelle ou en groupe, génération automatique du dossard,
     * application du code de rabais et envoi du mail de confirmation.
     * @author Ngoie Steven
     * @param  \Illuminate\Http\Request $request Données de l'inscription (course, groupe, options, tarif...).
     * @return \Illuminate\Http\JsonResponse Inscription créée (201) ou réactivée (200).
     */
    public function store(Request $request)
    {
        // Validation des données selon les règles métier et la structure de la base de données
        $validatedData = $request->validate([
            'id_course' => 'required|exists:Course,id',
            'id_participant' => 'nullable|exists:Participant,id',
            'id_groupe' => 'nullable|exists:Groupe,id',
            'id_document' => 'nullable|exists:Document,id',
            'id_ancienne_inscription' => 'nullable|exists:Inscription,id',
            'code_participant' => 'nullable|string|unique:Inscription,code_participant',
            'avertissement_valide' => 'sometimes|boolean',
            // Champ "en attente" utilisé pour les inscriptions relais / entreprises
            'status_paiement' => 'sometimes|in:Validé,Annulé,En attente,Échangé',
            'participe_challenge' => 'sometimes|boolean',
            'type_challenge'      => 'nullable|string|max:50',
            'equipe_challenge'    => 'nullable|string|max:100',
            'date_paiement'       => now(),
            'tarif'               => 'sometimes|numeric',
            'montant_rabais' => 'sometimes|numeric|min:0',
            'code_rabais'    => 'nullable|string|max:50',
        ]);

        $idParticipantConnecte = Auth::user()->participant->id;
        $idParticipant = $request->id_participant ?? $idParticipantConnecte;
        $course = Course::findOrFail($validatedData['id_course']);

        $course = Course::findOrFail($validatedData['id_course']);

        // Vérifie que les inscriptions sont encore ouvertes pour cette course
        if (!$course->isRegistrationOpen()) {
            return response()->json([
                'message' => 'Les inscriptions pour cette course sont clôturées.'
            ], 403);
        }

        // Détermine si le participant est directement rattaché au compte connecté (sans compte propre)
        $participant = Participant::find($idParticipant);
        $estGereParLeCompte = $participant && $participant->id_user === Auth::id();

        // Règle métier : en inscription de groupe, chaque membre reçoit le statut "En attente"
        // jusqu'à ce qu'il accepte l'invitation, sauf les profils gérés directement par le compte
        // (ils n'ont pas de compte propre et sont validés automatiquement).
        if (!empty($validatedData['id_groupe']) && !$estGereParLeCompte) {
            $statutInscription = 'En attente';
        } else {
            $statutInscription = 'Validé';
        }

        $statutPaiementFinal = $validatedData['status_paiement'] ?? $statutInscription;

        // Bloque l'inscription si la course exige l'acceptation d'un avertissement et qu'il n'est pas validé
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
            // Bloque la réinscription si une inscription active ou en attente existe déjà
            if (in_array($inscriptionExistante->status_paiement, ['En attente', 'Validé'])) {
                return response()->json([
                    'message' => 'Vous êtes déjà inscrit à cette course (ou votre inscription est en attente de paiement).'
                ], 409);
            }

            // Cas de réinscription : l'inscription précédente avait été annulée, on la réactive
            if ($inscriptionExistante->status_paiement === 'Annulé') {
                $inscriptionExistante->update([
                    'id_groupe' => $validatedData['id_groupe'] ?? null,
                    'id_document' => $validatedData['id_document'] ?? null,
                    'id_ancienne_inscription' => $validatedData['id_ancienne_inscription'] ?? null,
                    'code_participant' => $validatedData['code_participant'] ?? null,
                    'tarif' => $course->tarif,
                    'date_paiement' => now(),
                    'status_paiement' => $statutPaiementFinal,
                    'montant_rabais' => $validatedData['montant_rabais'] ?? 0,
                    'avertissement_valide' => $validatedData['avertissement_valide'] ?? false,
                ]);

                // Incrémente le compteur d'utilisations du code de rabais si un code valide a été fourni
                if (!empty($validatedData['code_rabais'])) {
                    $codeRabais = CodeRabais::where('code', strtoupper($validatedData['code_rabais']))
                        ->where('id_course', $course->id)
                        ->first();
                    if ($codeRabais) {
                        $codeRabais->increment('utilisations_actuelles');
                    }
                }

                // Génère le dossard automatiquement en cas de réinscription si aucun dossard n'existe encore
                if ($course->is_dossard == 0 && !$inscriptionExistante->dossard) {
                    try {
                        $this->genererDossardAutomatique($course, $inscriptionExistante->id);
                    } catch (\Exception $e) {
                        // Annule la réinscription si plus aucun dossard n'est disponible
                        $inscriptionExistante->update(['status_paiement' => 'Annulé']);
                        return response()->json([
                            'code' => 'DOSSARD_LIMIT_REACHED',
                            'message' => $e->getMessage()
                        ], 400);
                    }
                }

                // Retourne 200 (modification) plutôt que 201 (création)
                return response()->json($inscriptionExistante->load(['course', 'dossard']), 200);
            }
        }

        // Création d'une nouvelle inscription (aucun historique existant)
        $inscription = Inscription::create([
            'id_course' => $validatedData['id_course'],
            'id_participant' => $idParticipant,
            'id_groupe' => $validatedData['id_groupe'] ?? null,
            'id_document' => $validatedData['id_document'] ?? null,
            'id_ancienne_inscription' => $validatedData['id_ancienne_inscription'] ?? null,
            'code_participant' => $validatedData['code_participant'] ?? null,
            'tarif' => $validatedData['tarif'] ?? $course->tarif,
            'date_paiement'       => now(),
            'status_paiement' => $statutPaiementFinal,
            'montant_rabais' => $validatedData['montant_rabais'] ?? 0,
            'avertissement_valide' => $validatedData['avertissement_valide'] ?? false,
            'participe_challenge' => $validatedData['participe_challenge'] ?? false,
            'type_challenge' => $validatedData['type_challenge'] ?? null,
            'equipe_challenge' => $validatedData['equipe_challenge'] ?? null,
        ]);

        // Incrémente le compteur d'utilisations du code de rabais si un code valide a été fourni
        if (!empty($validatedData['code_rabais'])) {
            $codeRabais = CodeRabais::where('code', strtoupper($validatedData['code_rabais']))
                ->where('id_course', $course->id)
                ->first();
            if ($codeRabais) {
                $codeRabais->increment('utilisations_actuelles');
            }
        }

        // Génère automatiquement un dossard si la course n'utilise pas la personnalisation manuelle
        if ($course->is_dossard == 0) {
            try {
                $this->genererDossardAutomatique($course, $inscription->id);
            } catch (\Exception $e) {
                // Supprime l'inscription créée pour ne pas laisser un enregistrement sans dossard
                $inscription->delete();

                return response()->json([
                    'code' => 'DOSSARD_LIMIT_REACHED',
                    'message' => $e->getMessage()
                ], 400);
            }
        }

        // Envoi du mail de confirmation au participant (l'inscription n'est pas bloquée en cas d'échec)
        try {
            $inscriptionAvecDetails = $inscription->load([
                'course.evenement',
                'participant.user',
                'dossard',
                'groupe',
            ]);

            if ($inscriptionAvecDetails->participant->user?->email) {
                Mail::to($inscriptionAvecDetails->participant->user->email)
                    ->send(new ConfirmationInscriptionMail($inscriptionAvecDetails));
            }
        } catch (\Exception $e) {
            // Le mail est non-bloquant : on log l'erreur sans interrompre le flux
            \Log::error("Erreur envoi mail confirmation inscription : " . $e->getMessage());
        }

        return response()->json([
            'message' => 'Inscription enregistrée avec succès.',
            'inscription' => [
                'id' => $inscription->id,
            ],
        ], 201);
    }

    /**
     * Génère et attribue automatiquement le prochain numéro de dossard disponible pour une course.
     * Les bornes sont lues depuis la configuration de la course ; des valeurs par défaut sont
     * appliquées si elles sont absentes ou nulles. Lance une exception si la limite est atteinte.
     * @author Ngoie Steven
     * @param  Course $course       Course pour laquelle générer le dossard.
     * @param  int    $idInscription Identifiant de l'inscription à associer au dossard.
     * @return void
     * @throws \Exception Si tous les numéros de la plage sont déjà attribués.
     */
    private function genererDossardAutomatique($course, $idInscription)
    {
        // Tolère les valeurs null/0 en appliquant des bornes par défaut pour éviter les plantages
        $premier = ($course->premier_dossard && $course->premier_dossard > 0) ? $course->premier_dossard : 1;
        $dernier = ($course->dernier_dossard && $course->dernier_dossard > 0) ? $course->dernier_dossard : 99999;

        // Cherche le numéro le plus élevé déjà attribué pour cette course
        $maxNumeroActuel = Dossard::whereHas('inscription', function ($query) use ($course) {
            $query->where('id_course', $course->id);
        })->max('numero');

        // Calcule le prochain numéro : max + 1, ou premier dossard de la plage si aucun n'existe
        $prochainNumero = $maxNumeroActuel ? $maxNumeroActuel + 1 : $premier;

        if ($prochainNumero <= $dernier) {
            Dossard::create([
                'numero' => $prochainNumero,
                'id_inscription' => $idInscription,
                'retrait_dossard' => 0
            ]);
        } else {
            // Remonte une exception pour bloquer l'inscription si la plage est épuisée
            throw new \Exception("Désolé, il n'y a plus de dossards disponibles pour cette course (Limite fixée à {$dernier}).");
        }
    }

    /**
     * Retourne le détail d'une inscription spécifique.
     * Accessible à l'administrateur ou au participant propriétaire de l'inscription.
     * @author Ngoie Steven
     * @param  int $id Identifiant de l'inscription.
     * @return \Illuminate\Http\JsonResponse Détail de l'inscription ou 403 si accès non autorisé.
     */
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

        // Un participant ne peut consulter que sa propre inscription
        if (!$isAdmin && $inscription->id_participant !== $user->participant->id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        return response()->json($inscription);
    }

    /**
     * Met à jour une inscription existante depuis la vue administrateur.
     * Permet de modifier le statut de paiement, le tarif, les informations du participant, etc.
     * Protège contre la réactivation d'une inscription annulée.
     * @author Ngoie Steven
     * @param  \Illuminate\Http\Request $request Champs à mettre à jour.
     * @param  int                      $id      Identifiant de l'inscription.
     * @return \Illuminate\Http\JsonResponse Inscription mise à jour avec ses relations.
     */
    public function updateAdmin(Request $request, $id)
    {
        $inscription = Inscription::findOrFail($id);

        // Protection métier : un admin ne peut pas réactiver une inscription annulée via ce endpoint
        if ($inscription->status_paiement === 'Annulé' && $request->has('status_paiement') && $request->status_paiement !== 'Annulé') {
            return response()->json([
                'message' => 'Impossible de modifier le statut de paiement d\'une inscription annulée.'
            ], 400);
        }

        $validatedData = $request->validate([
            'participant_prenom' => 'sometimes|string|max:100',
            'participant_nom' => 'sometimes|string|max:100',
            'status_paiement' => 'sometimes|in:Validé,En attente,Annulé,Transféré,Échangé',
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

        // Met à jour le nom/prénom directement sur le modèle Participant si fournis
        if (array_key_exists('participant_prenom', $validatedData) || array_key_exists('participant_nom', $validatedData)) {
            $participantData = [];
            if (array_key_exists('participant_prenom', $validatedData)) {
                $participantData['prenom'] = $validatedData['participant_prenom'];
            }
            if (array_key_exists('participant_nom', $validatedData)) {
                $participantData['nom'] = $validatedData['participant_nom'];
            }
            if (!empty($participantData) && $inscription->participant) {
                $inscription->participant->update($participantData);
            }
        }

        // Retire les champs participant des données d'inscription avant la mise à jour
        unset($validatedData['participant_prenom'], $validatedData['participant_nom']);

        $inscription->update($validatedData);

        return response()->json($inscription->load(['course', 'participant', 'dossard']));
    }

    /**
     * Supprime définitivement une inscription (réservé à l'administrateur).
     * @author Ngoie Steven
     * @param  int $id Identifiant de l'inscription à supprimer.
     * @return \Illuminate\Http\JsonResponse Message de confirmation.
     */
    public function destroyAdmin($id)
    {
        $inscription = Inscription::findOrFail($id);

        $inscription->delete();

        return response()->json(['message' => 'Inscription supprimée avec succès.']);
    }

    /**
     * Met à jour une inscription depuis la vue participant.
     * Seuls le groupe, le document, le code participant et les options sont modifiables.
     * Les options existantes sont remplacées intégralement (suppression puis recréation).
     * @author Ngoie Steven
     * @author Neris Alessandro (gestion des choix d'options)
     * @param  \Illuminate\Http\Request $request Champs à mettre à jour.
     * @param  int                      $id      Identifiant de l'inscription.
     * @return \Illuminate\Http\JsonResponse Inscription mise à jour avec ses relations.
     */
    public function updateParticipant(Request $request, $id)
    {
        $user = Auth::user();
        $inscription = Inscription::findOrFail($id);

        // Vérifie que les inscriptions sont encore ouvertes pour cette course
        if (!$inscription->course->isRegistrationOpen()) {
            return response()->json([
                'message' => 'Les inscriptions pour cette course sont clôturées.'
            ], 403);
        }

        // Un participant ne peut modifier que sa propre inscription
        if ($inscription->id_participant !== $user->participant->id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $validatedData = $request->validate([
            'id_groupe' => 'sometimes|nullable|exists:Groupe,id',
            'id_document' => 'sometimes|nullable|exists:Document,id',
            'code_participant' => 'sometimes|nullable|string|unique:Inscription,code_participant,' . $inscription->id,
            'choix_options' => 'sometimes|array',
            'choix_options.*.id_option' => 'required_with:choix_options|exists:Options,id',
            'choix_options.*.quantite' => 'sometimes|integer|min:0',
            // Le participant peut voir son statut mais ne peut pas le modifier lui-même
            'status_paiement' => 'sometimes|in:Validé,En attente,Annulé,Transféré,Échangé',
        ]);

        // Met à jour uniquement les champs simples autorisés
        $inscription->update(array_intersect_key($validatedData, array_flip(['id_groupe', 'id_document', 'code_participant'])));

        // Remplacement complet des options : suppression de l'existant puis création des nouveaux choix
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

    /**
     * Annule une inscription depuis la vue participant.
     * Ne supprime pas l'enregistrement en base de données mais passe son statut à "Annulé".
     * Si le groupe associé n'a plus aucune inscription active, il est automatiquement supprimé
     * (sauf pour les groupes de type Entreprise qui sont partagés entre plusieurs courses).
     * @author Ngoie Steven
     * @author Guillermet Jean-Daniel (suppression automatique du groupe orphelin)
     * @param  \Illuminate\Http\Request $request Peut contenir le paramètre `is_change` (changement de course).
     * @param  int                      $id      Identifiant de l'inscription à annuler.
     * @return \Illuminate\Http\JsonResponse Confirmation d'annulation avec l'inscription mise à jour.
     */
    public function destroyParticipant(Request $request, $id)
    {
        $user = Auth::user();
        $inscription = Inscription::findOrFail($id);

        // Vérifie que les inscriptions sont encore ouvertes pour cette course
        if (!$inscription->course->isRegistrationOpen()) {
            return response()->json([
                'message' => 'Les inscriptions pour cette course sont clôturées.'
            ], 403);
        }

        // Un participant ne peut annuler que sa propre inscription
        if ($inscription->id_participant !== $user->participant->id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        // Indique si l'annulation fait partie d'un changement de course (Upgrade/Downgrade)
        $isChange = $request->query('is_change') == 1;

        // Autorise l'annulation d'une inscription payée UNIQUEMENT dans le cadre d'un changement de course
        if ($inscription->status_paiement === 'Validé' && !$isChange) {
            return response()->json([
                'message' => 'Impossible d\'annuler une inscription déjà payée. Veuillez contacter l\'organisateur pour toute demande d\'annulation ou de remboursement.'
            ], 400);
        }

        // Passe le statut à "Annulé" sans supprimer l'enregistrement (la suppression physique est réservée à l'admin)
        $inscription->update(['status_paiement' => 'Annulé']);

        // Suppression du groupe si toutes ses inscriptions sont désormais annulées ou transférées.
        // Les groupes de type Entreprise sont partagés entre plusieurs inscriptions et ne doivent pas être supprimés.
        $idGroupe = $inscription->id_groupe;
        if ($idGroupe) {
            $autresActives = Inscription::where('id_groupe', $idGroupe)
                ->whereIn('status_paiement', ['Validé', 'En attente'])
                ->where('id', '!=', $inscription->id)
                ->count();

            if ($autresActives === 0) {
                $groupe = Groupe::find($idGroupe);
                if ($groupe && $groupe->type !== 'Entreprise') {
                    $groupe->participants()->detach();
                    $groupe->delete();
                }
            }
        }

        return response()->json([
            'message' => 'Inscription annulée avec succès.',
            'inscription' => $inscription
        ]);
    }

    /**
     * Exporte toutes les inscriptions filtrées au format Excel (xlsx) ou CSV.
     * Les filtres actifs dans l'interface admin sont répercutés dans le fichier exporté.
     * @author Ngoie Steven
     * @author Neris Alessandro (application des filtres sur l'export)
     * @param  \Illuminate\Http\Request $request Paramètres : `format` (xlsx|csv), `recherche`, `status`, `type`.
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse Fichier téléchargeable.
     */
    public function exportAdmin(Request $request)
    {
        $user = Auth::user();
        if (!$user->roles()->where('type', 'Administrateur')->exists()) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        // Récupère le format depuis l'URL (csv ou xlsx), xlsx par défaut
        $format = $request->query('format', 'xlsx');
        $preset = $request->query('preset', 'logistique');
        $filters = $request->only(['recherche', 'status', 'type']);
        $inscriptions = $this->adminInscriptionsQuery([])->get();
        $inscriptions = $inscriptions
            ->filter(fn ($item) => $this->matchesAdminFilters($item, $filters))
            ->sortByDesc('date_paiement')
            ->values();

        $extension = $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
        $fileName = 'export_inscriptions_' . $preset . '_' . date('Y-m-d_H-i') . '.' . $format;

        return Excel::download(new InscriptionsExport($inscriptions, $preset), $fileName, $extension);
    }

    /**
     * Construit la requête Eloquent de base pour les inscriptions dans la vue administrateur.
     * Charge toutes les relations nécessaires et applique les filtres de recherche, de statut et de type.
     * Extraite en méthode privée pour être réutilisée par indexAdmin et exportAdmin.
     * @author Neris Alessandro
     * @param  array<string, mixed> $filters Filtres optionnels : `recherche`, `status`, `type`.
     * @return \Illuminate\Database\Eloquent\Builder Requête prête à être exécutée.
     */
    private function adminInscriptionsQuery(array $filters = [])
    {
        // Colonnes sélectionnées pour le participant : on exclut la photo pour alléger la réponse
        $participantColumns = [
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
        ];

        $query = Inscription::with([
            'course.evenement',
            'participant' => fn ($query) => $query->select($participantColumns),
            'participant.user' => fn ($query) => $query->select('id', 'email'),
            'dossard',
            'groupe',
            'choixOptions.option',
            'reponsesQuestions.question',
            'reponsesQuestions.option',
            'documentsFournis',
            'ancienneInscription.course',
            'ancienneInscription.participant' => fn ($query) => $query->select($participantColumns),
            'ancienneInscription.participant.user' => fn ($query) => $query->select('id', 'email'),
            'ancienneInscription.groupe',
        ])->orderBy('date_paiement', 'desc');

        // Filtre full-text sur le nom, prénom, numéro de dossard, groupe, équipe challenge, course et événement
        $recherche = trim((string) ($filters['recherche'] ?? ''));
        if ($recherche !== '') {
            $motCle = '%' . $recherche . '%';

            $query->where(function (Builder $subQuery) use ($motCle) {
                $subQuery->whereHas('participant', function (Builder $participantQuery) use ($motCle) {
                    $participantQuery->where('nom', 'like', $motCle)
                        ->orWhere('prenom', 'like', $motCle);
                })
                ->orWhereHas('dossard', function (Builder $dossardQuery) use ($motCle) {
                    $dossardQuery->where('numero', 'like', $motCle);
                })
                ->orWhereHas('groupe', function (Builder $groupeQuery) use ($motCle) {
                    $groupeQuery->where('nom', 'like', $motCle);
                })
                ->orWhere('equipe_challenge', 'like', $motCle)
                ->orWhereHas('course', function (Builder $courseQuery) use ($motCle) {
                    $courseQuery->where('nom', 'like', $motCle)
                        ->orWhereHas('evenement', function (Builder $evenementQuery) use ($motCle) {
                            $evenementQuery->where('nom', 'like', $motCle);
                        });
                });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status_paiement', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->whereHas('course', function (Builder $courseQuery) use ($filters) {
                $courseQuery->where('type', $filters['type']);
            });
        }

        return $query;
    }

    /**
     * Formate les memberships approuvés dans une structure compatible avec le tableau des inscriptions admin.
     * Permet d'afficher les memberships et les inscriptions courses dans une seule liste unifiée.
     * @author Ngoie Steven
     * @param  array $participantColumns Colonnes à sélectionner pour le participant (optionnel, utilise la liste par défaut si vide).
     * @return \Illuminate\Support\Collection Collection de tableaux normalisés représentant chaque membership.
     */
    private function adminMembershipRows(array $participantColumns = []): Collection
    {
        // Utilise la liste de colonnes par défaut si aucune n'est fournie
        if (empty($participantColumns)) {
            $participantColumns = [
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
            ];
        }

        $memberships = FormulaireMembership::query()
            ->with([
                'invitation.participantUser' => function ($query) use ($participantColumns) {
                    $query->select('id', 'email')
                        ->with([
                            'participant' => fn ($participantQuery) => $participantQuery->select($participantColumns),
                        ]);
                },
            ])
            ->where('status', 'Approuvée')
            ->orderByDesc('date_decision')
            ->get();

        // Transforme chaque membership en une structure identique à celle d'une inscription standard
        return $memberships->map(function (FormulaireMembership $membership) {
            $participantUser = $membership->invitation?->participantUser;
            $participant = $participantUser?->participant;
            $datePaiement = $membership->date_decision ?? $membership->date_creation;
            $datePaiementString = $datePaiement?->format('Y-m-d H:i:s');

            return [
                'id' => 'membership-' . $membership->id,
                'membership_id' => $membership->id,
                'course' => [
                    'id' => null,
                    'nom' => 'Inscription à Membership',
                    'type' => 'Membership',
                    'distance' => null,
                    'status' => 'actif',
                    'evenement' => [
                        'id' => null,
                        'nom' => 'Membership',
                        'couleur_primaire' => '#eef2ff',
                        'couleur_secondaire' => '#0e0f54',
                    ],
                ],
                'participant' => [
                    'id' => $participant?->id,
                    // Fallback sur les données du formulaire si le participant n'a pas encore de compte
                    'nom' => $participant?->nom ?? $membership->nom,
                    'prenom' => $participant?->prenom ?? $membership->prenom,
                    'date_naissance' => $participant?->date_naissance,
                    'equipe_nom' => $participant?->equipe_nom,
                    'adresse' => $membership->adresse,
                    'code_postal' => $membership->code_postal,
                    'ville' => $membership->ville,
                    'pays' => $membership->pays,
                    'telephone' => $membership->telephone,
                    'nationalite' => $participant?->nationalite,
                    'instagram' => $participant?->instagram,
                    'facebook' => $participant?->facebook,
                    'taille_tshirt' => $participant?->taille_tshirt,
                    'sexe' => $participant?->sexe,
                    'user' => [
                        'email' => $participantUser?->email ?? $membership->email,
                    ],
                ],
                'dossard' => null,
                'groupe' => null,
                'choixOptions' => [],
                'reponsesQuestions' => [],
                'documentsFournis' => [],
                'ancienneInscription' => null,
                'date_paiement' => $datePaiementString,
                'tarif' => $membership->prix ?? 25,
                'status_paiement' => 'Validé',
                'montant_rabais' => 0,
                'avertissement_valide' => true,
                'participe_challenge' => false,
                'type_challenge' => null,
                'equipe_challenge' => null,
                'code_participant' => null,
                'ref_groupage' => null,
                // Numéro d'inscription préfixé "M" pour distinguer les memberships des inscriptions courses
                'numero_inscription' => 'M' . str_pad((string) $membership->id, 4, '0', STR_PAD_LEFT),
                'id_course' => null,
                'is_membership' => true,
            ];
        });
    }

    /**
     * Vérifie si un enregistrement (inscription ou membership) correspond aux filtres actifs.
     * Utilisé pour filtrer côté PHP les résultats après fusion inscriptions + memberships,
     * car les memberships ne sont pas en base Inscription et ne peuvent pas être filtrés en SQL.
     * @author Ngoie Steven
     * @param  mixed                $item    Inscription ou tableau membership normalisé.
     * @param  array<string, mixed> $filters Filtres actifs : `recherche`, `status`, `type`.
     * @return bool True si l'enregistrement correspond aux critères, false sinon.
     */
    private function matchesAdminFilters($item, array $filters): bool
    {
        $recherche = trim((string) ($filters['recherche'] ?? ''));
        $status = (string) ($filters['status'] ?? '');
        $type = (string) ($filters['type'] ?? '');

        if ($status !== '' && data_get($item, 'status_paiement') !== $status) {
            return false;
        }

        if ($type !== '' && data_get($item, 'course.type') !== $type) {
            return false;
        }

        if ($recherche === '') {
            return true;
        }

        // Concatène tous les champs recherchables en une seule chaîne pour simplifier la comparaison
        $needle = mb_strtolower($recherche);
        $haystack = implode(' ', array_filter([
            mb_strtolower((string) data_get($item, 'participant.nom', '')),
            mb_strtolower((string) data_get($item, 'participant.prenom', '')),
            mb_strtolower((string) data_get($item, 'participant.user.email', '')),
            mb_strtolower((string) data_get($item, 'dossard.numero', '')),
            mb_strtolower((string) data_get($item, 'groupe.nom', '')),
            mb_strtolower((string) data_get($item, 'equipe_challenge', '')),
            mb_strtolower((string) data_get($item, 'course.nom', '')),
            mb_strtolower((string) data_get($item, 'course.evenement.nom', '')),
            mb_strtolower((string) data_get($item, 'course.type', '')),
        ]));

        return str_contains($haystack, $needle);
    }
}