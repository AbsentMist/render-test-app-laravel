<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Inscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    // GET (PARTICIPANT) - Récupérer le document pour une inscription
    public function indexByInscription($id_inscription): JsonResponse
    {
        $user = Auth::user();
        $inscription = Inscription::findOrFail($id_inscription);

        // Vérifier que le participant propriétaire peut accéder
        if ($inscription->id_participant !== $user->participant->id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $documents = Document::where('id_inscription', $id_inscription)->get();

        return response()->json($documents);
    }

    // POST (PARTICIPANT) - Uploader un document pour une inscription
    public function storeForInscription(Request $request, $id_inscription): JsonResponse
    {
        $user = Auth::user();
        $inscription = Inscription::findOrFail($id_inscription);

        // Vérifier que le participant propriétaire peut accéder
        if ($inscription->id_participant !== $user->participant->id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $validatedData = $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // Max 10MB
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
        ]);

        // Supprimer l'ancien document s'il existe
        $oldDocument = Document::where('id_inscription', $id_inscription)->first();
        if ($oldDocument && $oldDocument->url) {
            Storage::disk('documents')->delete($oldDocument->url);
            $oldDocument->delete();
        }

        // Stocker le nouveau fichier
        $path = $request->file('file')->store('inscriptions/' . $id_inscription, 'documents');

        // Créer le document
        $document = Document::create([
            'id_inscription' => $id_inscription,
            'id_participant' => $inscription->id_participant,
            'url' => $path,
            'date_debut' => $validatedData['date_debut'] ?? null,
            'date_fin' => $validatedData['date_fin'] ?? null,
            'valable' => true,
        ]);

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
        $isAdmin = $user->roles()->where('type', 'Administrateur')->exists();

        if (!$isAdmin && $document->id_participant !== $user->participant->id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        if (!Storage::disk('documents')->exists($document->url)) {
            return response()->json(['message' => 'Fichier introuvable.'], 404);
        }

        return Storage::disk('documents')->download($document->url);
    }

    // DELETE (PARTICIPANT) - Supprimer un document
    public function destroyParticipant($id): JsonResponse
    {
        $user = Auth::user();
        $document = Document::findOrFail($id);

        if ($document->id_participant !== $user->participant->id) {
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
