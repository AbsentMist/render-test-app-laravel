<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Mail\InvitationParticipantMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ===== INSCRIPTION =====
    public function register(Request $request)
    {
        $request->validate([
            'email'          => 'required|email|max:80|unique:User,email',
            'password' => ['required', 'confirmed', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/'],
            'nom'            => 'required|string|max:100',
            'prenom'         => 'required|string|max:100',
            'date_naissance' => 'required|date',
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
        ]);

        $user = User::create([
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

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

    // ===== CONNEXION =====
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

    // ===== DÉCONNEXION =====
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie.'
        ]);
    }

    // ===== UTILISATEUR CONNECTÉ =====
    public function me(Request $request)
    {
        return response()->json($this->buildUserResponse($request->user()));
    }

    // ===== MODIFICATION MOT DE PASSE =====
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string|min:8|confirmed',
        ], [
            'newPassword.confirmed' => 'La confirmation du nouveau mot de passe ne correspond pas.',
        ]);

        $user = $request->user();

        if (!Hash::check($validated['currentPassword'], $user->password)) {
            return response()->json([
                'message' => 'Le mot de passe actuel est incorrect.',
                'errors' => ['currentPassword' => ['Le mot de passe actuel est incorrect.']],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['newPassword']),
        ]);

        return response()->json(['message' => 'Mot de passe modifié avec succès.']);
    }

    private function buildUserResponse(User $user): User
    {
        $loadedUser = $user->load('participant', 'roles');

        if ($loadedUser->participant && $loadedUser->participant->photo) {
            $loadedUser->participant->photo = 'data:image/jpeg;base64,' . base64_encode($loadedUser->participant->photo);
        }

        return $loadedUser;
    }

    // ===== RECHERCHE PARTICIPANT PAR EMAIL =====
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

        // Ne pas exposer les infos sensibles
        $participant = $user->participant;
        return response()->json([
            'id'     => $participant->id,
            'prenom' => $participant->prenom,
            'nom'    => $participant->nom,
            'email'  => $user->email,
        ]);
    }

    //Invite un utilisateur (sans mot de passe) et créer un participant associé
    public function createInvitedUser(Request $request)
    {
        // Validation (sans password)
        $request->validate([
            'email'          => 'required|email|max:80|unique:User,email',
            'nom'            => 'required|string|max:100',
            'prenom'         => 'required|string|max:100',
            'date_naissance' => 'required|date',
            'telephone'      => 'required|string|max:20|unique:Participant,telephone',
            'nationalite'    => 'nullable|string|max:100', //nullable car pas demandé dans le formulaire frontend
            'adresse'        => 'required|string|max:100',
            'code_postal'    => 'required|string|max:10',
            'ville'          => 'required|string|max:100',
            'pays'           => 'required|string|max:100',
            'taille_tshirt'  => 'required|string|max:10',
            'sexe'           => 'required|string|max:10',
        ]);

        // Création d'un User avec un mot de passe aléatoire sécurisé
        $randomPassword = \Illuminate\Support\Str::password(16, true, true, true, false);
        
        $user = User::create([
            'email'    => $request->email,
            'password' => Hash::make($randomPassword),
        ]);

        // Création du Participant associé
        $participant = Participant::create([
            'id_user'        => $user->id,
            'nom'            => $request->nom,
            'prenom'         => $request->prenom,
            'date_naissance' => $request->date_naissance,
            'telephone'      => $request->telephone,
            //On met la nationalité à celle du pays par défaut, le frontend demandera la nationalité lors de la validation du participant
            'nationalite'    => $request->nationalite ?? $request->pays, 
            'adresse'        => $request->adresse,
            'code_postal'    => $request->code_postal,
            'ville'          => $request->ville,
            'pays'           => $request->pays,
            'taille_tshirt'  => $request->taille_tshirt,
            'sexe'           => $request->sexe,
        ]);

        // Envoi de l'email avec le lien pour définir le mot de passe 
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(
                new \App\Mail\InvitationParticipantMail($participant->load('user'), $randomPassword)
            );
        } catch (\Exception $e) {
            // Si l'email échoue à être envoyé, on ne bloque pas la création du participant, mais log d'erreur.
            // On se contente d'écrire l'erreur dans les logs pour le débogage.
            \Illuminate\Support\Facades\Log::error("Erreur d'envoi d'email d'invitation : " . $e->getMessage());
        }

        //Renvoie les infos du participant invité au frontend
        return response()->json([
            'message' => 'Utilisateur invité créé avec succès.',
            'participant' => [
                'id'     => $participant->id,
                'prenom' => $participant->prenom,
                'nom'    => $participant->nom,
                'email'  => $user->email,
            ]
        ], 201);
    }

    // Récupère tous les participants liés au compte connecté
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

// Crée un nouveau participant (Sous-profil ou profil participant + compte Indépendant si email fourni)
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
            // L'email doit être un email valide et unique dans la table User
            'email'          => 'nullable|email|max:80|unique:User,email',
            'taille_tshirt'  => 'nullable|string|max:10',
            'sexe'           => 'nullable|string|max:10',
        ]);

        $idUserRattachement = $request->user()->id; // Par défaut : sous-profil du compte connecté
        $nouveauUser = null;
        $randomPassword = null;

        // Si un email est fourni, on crée un VRAI compte indépendant
        if ($request->filled('email')) {
            $randomPassword = \Illuminate\Support\Str::password(16, true, true, true, false);
            
            $nouveauUser = User::create([
                'email'    => $request->email,
                'password' => Hash::make($randomPassword),
            ]);

            $idUserRattachement = $nouveauUser->id; // On rattache le futur participant à ce nouveau compte
        }

        // Conversion date naissance format MYSQL/MariaDB
        $dateNaissance = $request->date_naissance;
        
        // On vérifie que la date n'est pas vide et qu'elle contient un slash (selon règle du frontend)
        if ($dateNaissance && str_contains($dateNaissance, '/')) {
            $dateNaissance = \Carbon\Carbon::createFromFormat('d/m/Y', $dateNaissance)->format('Y-m-d');
        }
        // Création du participant
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

        // ENVOI DE L'EMAIL à l'user
        if ($nouveauUser) {
            try {
                // L'envoi se fera dans les logs en local grâce à ton fichier .env (MAIL_MAILER=log)
                \Illuminate\Support\Facades\Mail::to($nouveauUser->email)->send(
                    new \App\Mail\InvitationParticipantMail($participant->load('user'), $randomPassword)
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Erreur d'envoi d'email d'invitation au nouveau compte : " . $e->getMessage());
            }
        }

        return response()->json($participant, 201);
    }
    
}