<?php

/**
 * @fileoverview DocumentController.php
 * @description Contrôleur gérant l'upload, le téléchargement et la suppression des documents
 *              fournis par les participants lors de l'inscription (certificats médicaux, attestations...).
 *              Les fichiers sont stockés sur un disque dédié "documents" (Storage::disk).
 *              Les autorisations d'accès sont gérées via canManageInscriptionDocuments() qui
 *              autorise : le participant propriétaire, les sous-profils du même compte,
 *              et le fondateur du groupe pour les membres de son groupe.
 * @author Neris Alessandro
 */

namespace App\Http\Controllers;

use App\Enums\StatutParticipant;
use App\Models\Document;
use App\Models\Inscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Vérifie si l'utilisateur connecté est autorisé à gérer les documents d'une inscription.
     * Trois cas sont autorisés :
     *   1. L'utilisateur est le participant propriétaire de l'inscription
     *   2. L'utilisateur gère le profil participant (sous-profil du même compte)
     *   3. L'utilisateur est fondateur du groupe de l'inscription
     * @author Neris Alessandro
     * @param  mixed       $user        Utilisateur connecté.
     * @param  Inscription $inscription Inscription dont on vérifie l'accès.
     * @return bool True si l'accès est autorisé.
     */
    private function canManageInscriptionDocuments($user, Inscription $inscription): bool
    {
        if (!$user || !$user->participant) {
            return false;
        }

        $idParticipantConnecte = $user->participant->id;

        // Cas 1 : le participant est directement propriétaire de l'inscription
        if ((int) $inscription->id_participant === (int) $idParticipantConnecte) {
            return true;
        }

        // Cas 2 : le participant est un sous-profil géré par le même compte utilisateur
        if ($inscription->participant && (int) $inscription->participant->id_user === (int) $user->id) {
            return true;
        }

        if (!$inscription->id_groupe) {
            return false;
        }

        $isMemberOfGroup = DB::table('GroupeParticipant')
            ->where('id_groupe', $inscription->id_groupe)
            ->where('id_participant', $idParticipantConnecte)
            ->exists();

        if (!$isMemberOfGroup) {
            return false;
        }

        // Cas 3 : en groupe, seul le fondateur peut gérer les documents des autres membres
        return DB::table('GroupeParticipant')
            ->where('id_groupe', $inscription->id_groupe)
            ->where('id_participant', $idParticipantConnecte)
            ->whereRaw('LOWER(statut) = ?', [strtolower(StatutParticipant::FONDATEUR->value)])
            ->exists();
    }

    /**
     * Vérifie si l'utilisateur connecté est administrateur.
     * @author Neris Alessandro
     * @param  mixed $user Utilisateur à vérifier.
     * @return bool True si l'utilisateur possède le rôle Administrateur.
     */
    private function isAdmin($user): bool
    {
        return $user->roles()->where('type', 'Administrateur')->exists();
    }

    /**
     * Valide les données d'un fichier uploadé (format et taille).
     * Formats acceptés : PDF, JPG, JPEG, PNG. Taille max : 10 Mo.
     * @author Neris Alessandro
     * @param  Request $request Requête contenant le fichier.
     * @return array Données validées.
     */
    private function validateUpload(Request $request): array
    {
        return $request->validate([
            'file'       => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'date_debut' => 'nullable|date',
            'date_fin'   => 'nullable|date',
        ]);
    }

    /**
     * Remplace le document existant d'une inscription par un nouveau fichier.
     * L'ancien fichier est supprimé du disque avant l'upload du nouveau.
     * @author Neris Alessandro
     * @param  Inscription $inscription    Inscription cible.
     * @param  array       $validatedData  Données validées (dates de validité).
     * @param  Request     $request        Requête contenant le fichier.
     * @return Document Nouveau document créé.
     */
    private function replaceDocumentForInscription(Inscription $inscription, array $validatedData, Request $request): Document
    {
        // Supprime l'ancien document si existant
        $oldDocument = Document::where('id_inscription', $inscription->id)->first();
        if ($oldDocument && $oldDocument->url) {
            Storage::disk('documents')->delete($oldDocument->url);
            $oldDocument->delete();
        }

        $path = $request->file('file')->store('inscriptions/' . $inscription->id, 'documents');

        return Document::create([
            'id_inscription' => $inscription->id,
            'id_participant' => $inscription->id_participant,
            'url'            => $path,
            'date_debut'     => $validatedData['date_debut'] ?? null,
            'date_fin'       => $validatedData['date_fin'] ?? null,
            'valable'        => true,
        ]);
    }

    /**
     * Retourne le document associé à une inscription (vue participant).
     * @author Neris Alessandro
     * @param  int $id_inscription Identifiant de l'inscription.
     * @return JsonResponse Liste des documents ou 403/404.
     */
    public function indexByInscription($id_inscription): JsonResponse
    {
        $user        = Auth::user();
        $inscription = Inscription::with('participant')->findOrFail($id_inscription);

        if (!$this->canManageInscriptionDocuments($user, $inscription)) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $documents = Document::where('id_inscription', $id_inscription)->get();

        return response()->json($documents);
    }

    /**
     * Upload un document pour une inscription (vue participant).
     * Vérifie que les inscriptions sont encore ouvertes avant d'accepter l'upload.
     * @author Neris Alessandro
     * @author Ngoie Steven (vérification isRegistrationOpen)
     * @param  Request $request        Requête avec le fichier.
     * @param  int     $id_inscription Identifiant de l'inscription cible.
     * @return JsonResponse Document créé (201) ou erreur.
     */
    public function storeForInscription(Request $request, $id_inscription): JsonResponse
    {
        $user        = Auth::user();
        $inscription = Inscription::with('participant')->findOrFail($id_inscription);

        // Vérifie que la fenêtre d'inscription n'est pas dépassée
        if (!$inscription->course->isRegistrationOpen()) {
            return response()->json([
                'message' => 'Impossible d\'ajouter un document, la date limite est dépassée.'
            ], 403);
        }

        if (!$this->canManageInscriptionDocuments($user, $inscription)) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $validatedData = $this->validateUpload($request);
        $document      = $this->replaceDocumentForInscription($inscription, $validatedData, $request);

        return response()->json([
            'message'  => 'Document uploadé avec succès.',
            'document' => $document
        ], 201);
    }

    /**
     * Upload un document pour une inscription (vue administrateur).
     * @author Neris Alessandro
     * @param  Request $request        Requête avec le fichier.
     * @param  int     $id_inscription Identifiant de l'inscription cible.
     * @return JsonResponse Document créé (201) ou 403.
     */
    public function storeForInscriptionAdmin(Request $request, $id_inscription): JsonResponse
    {
        $user = Auth::user();
        if (!$this->isAdmin($user)) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $inscription   = Inscription::findOrFail($id_inscription);
        $validatedData = $this->validateUpload($request);
        $document      = $this->replaceDocumentForInscription($inscription, $validatedData, $request);

        return response()->json([
            'message'  => 'Document uploadé avec succès.',
            'document' => $document
        ], 201);
    }

    /**
     * Télécharge un document (vue participant et admin).
     * Retourne le contenu binaire du fichier avec les headers de téléchargement.
     * @author Neris Alessandro
     * @param  int $id Identifiant du document.
     * @return mixed Réponse binaire ou erreur 403/404.
     */
    public function download($id): mixed
    {
        $document    = Document::findOrFail($id);
        $user        = Auth::user();
        $isAdmin     = $this->isAdmin($user);
        $inscription = $document->inscription()->with('participant')->first();

        if (!$isAdmin && (!$inscription || !$this->canManageInscriptionDocuments($user, $inscription))) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        if (!Storage::disk('documents')->exists($document->url)) {
            return response()->json(['message' => 'Fichier introuvable.'], 404);
        }

        $fileContent = Storage::disk('documents')->get($document->url);

        return response($fileContent, 200, [
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . basename($document->url) . '"',
        ]);
    }

    /**
     * Supprime un document et son fichier physique (vue participant).
     * @author Neris Alessandro
     * @param  int $id Identifiant du document à supprimer.
     * @return JsonResponse Confirmation ou 403.
     */
    public function destroyParticipant($id): JsonResponse
    {
        $user        = Auth::user();
        $document    = Document::findOrFail($id);
        $inscription = $document->inscription()->with('participant')->first();

        if (!$inscription || !$this->canManageInscriptionDocuments($user, $inscription)) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        if ($document->url && Storage::disk('documents')->exists($document->url)) {
            Storage::disk('documents')->delete($document->url);
        }

        $document->delete();

        return response()->json(['message' => 'Document supprimé avec succès.']);
    }

    /**
     * Supprime un document et son fichier physique (vue administrateur).
     * @author Neris Alessandro
     * @param  int $id Identifiant du document à supprimer.
     * @return JsonResponse Confirmation.
     */
    public function destroyAdmin($id): JsonResponse
    {
        $document = Document::findOrFail($id);

        if ($document->url && Storage::disk('documents')->exists($document->url)) {
            Storage::disk('documents')->delete($document->url);
        }

        $document->delete();

        return response()->json(['message' => 'Document supprimé avec succès.']);
    }
}