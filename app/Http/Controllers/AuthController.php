<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        // Création du User
        $user = User::create([
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Gestion photo
        $photo = null;
        if ($request->hasFile('photo')) {
            $photo = file_get_contents($request->file('photo')->getRealPath());
        }

        // Création du Participant avec le même id que User
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
            'user'  => $user->load('participant', 'roles'),
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
            'user'  => $user->load('participant', 'roles'),
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
        return response()->json($request->user()->load('participant', 'roles'));
    }
}