<?php

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
    private function canManageInscriptionDocuments($user, Inscription $inscription): bool
    {
        if (!$user || !$user->participant) {
            return false;
        }

        $idParticipantConnecte = $user->participant->id;

        if ((int) $inscription->id_participant === (int) $idParticipantConnecte) {
            return true;
        }

        // Un compte peut gérer les profils participants qui lui sont rattachés.
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

        // En groupe, seul le fondateur peut gérer les documents d'un autre membre.
        return DB::table('GroupeParticipant')
            ->where('id_groupe', $inscription->id_groupe)
            ->where('id_participant', $idParticipantConnecte)
            ->whereRaw('LOWER(statut) = ?', [strtolower(StatutParticipant::FONDATEUR->value)])
            ->exists();
    }

    private function isAdmin($user): bool
    {
        return $user->roles()->where('type', 'Administrateur')->exists();
    }

    private function validateUpload(Request $request): array
    {
        return $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
        ]);
    }

    private function replaceDocumentForInscription(Inscription $inscription, array $validatedData, Request $request): Document
    {
        $oldDocument = Document::where('id_inscription', $inscription->id)->first();
        if ($oldDocument && $oldDocument->url) {
            Storage::disk('documents')->delete($oldDocument->url);
            $oldDocument->delete();
        }

        $path = $request->file('file')->store('inscriptions/' . $inscription->id, 'documents');

        return Document::create([
            'id_inscription' => $inscription->id,
            'id_participant' => $inscription->id_participant,
            'url' => $path,
            'date_debut' => $validatedData['date_debut'] ?? null,
            'date_fin' => $validatedData['date_fin'] ?? null,
            'valable' => true,
        ]);
    }

    // GET (PARTICIPANT) - Récupérer le document pour une inscription
    public function indexByInscription($id_inscription): JsonResponse
    {
        $user = Auth::user();
        $inscription = Inscription::with('participant')->findOrFail($id_inscription);

        if (!$this->canManageInscriptionDocuments($user, $inscription)) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $documents = Document::where('id_inscription', $id_inscription)->get();

        return response()->json($documents);
    }

    // POST (PARTICIPANT) - Uploader un document pour une inscription
    public function storeForInscription(Request $request, $id_inscription): JsonResponse
    {
        $user = Auth::user();
        $inscription = Inscription::with('participant')->findOrFail($id_inscription);

        // Vérifier que l'inscription est toujours ouverte
        if (!$inscription->course->isRegistrationOpen()) {
            return response()->json([
                'message' => 'Impossible d\'ajouter un document, la date limite est dépassée.'
            ], 403);
        }

        if (!$this->canManageInscriptionDocuments($user, $inscription)) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $validatedData = $this->validateUpload($request);
        $document = $this->replaceDocumentForInscription($inscription, $validatedData, $request);

        return response()->json([
            'message' => 'Document uploadé avec succès.',
            'document' => $document
        ], 201);
    }

    // POST (ADMIN) - Uploader un document pour une inscription
    public function storeForInscriptionAdmin(Request $request, $id_inscription): JsonResponse
    {
        $user = Auth::user();
        if (!$this->isAdmin($user)) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $inscription = Inscription::findOrFail($id_inscription);
        $validatedData = $this->validateUpload($request);
        $document = $this->replaceDocumentForInscription($inscription, $validatedData, $request);

        return response()->json([
            'message' => 'Document uploadé avec succès.',
            'document' => $document
        ], 201);
    }

    // GET (PARTICIPANT & ADMIN) - Télécharger un document
    public function download($id): mixed
    {
        $document = Document::findOrFail($id);

        $user = Auth::user();
        $isAdmin = $this->isAdmin($user);
        $inscription = $document->inscription()->with('participant')->first();

        if (!$isAdmin && (!$inscription || !$this->canManageInscriptionDocuments($user, $inscription))) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        if (!Storage::disk('documents')->exists($document->url)) {
            return response()->json(['message' => 'Fichier introuvable.'], 404);
        }

        $fileContent = Storage::disk('documents')->get($document->url);

        return response($fileContent, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . basename($document->url) . '"',
        ]);
    }

    // DELETE (PARTICIPANT) - Supprimer un document
    public function destroyParticipant($id): JsonResponse
    {
        $user = Auth::user();
        $document = Document::findOrFail($id);
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

    // DELETE (ADMIN) - Supprimer un document
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
