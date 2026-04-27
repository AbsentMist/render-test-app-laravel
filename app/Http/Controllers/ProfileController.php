<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load('participant');
        $participant = $user->participant;

        if (!$participant) {
            return response()->json(['message' => 'Participant introuvable.'], 404);
        }

        [$adresse, $numero] = $this->splitAdresseNumero($participant->adresse);

        return response()->json([
            'nom' => $participant->nom,
            'prenom' => $participant->prenom,
            'email' => $user->email,
            'dateNaissance' => $participant->date_naissance
                ? Carbon::parse($participant->date_naissance)->format('d/m/Y')
                : null,
            'adresse' => $adresse,
            'numero' => $numero,
            'club' => $participant->equipe_nom,
            'npa' => $participant->code_postal,
            'commune' => $participant->ville,
            'nationalite' => $participant->nationalite,
            'telephone' => $participant->telephone,
            'tailleTshirt' => $participant->taille_tshirt,
            'photo' => $participant->photo ? 'data:image/jpeg;base64,'.base64_encode($participant->photo) : null,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user()->load('participant');
        $participant = $user->participant;

        if (!$participant) {
            return response()->json(['message' => 'Participant introuvable.'], 404);
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'max:80',
                Rule::unique('User', 'email')->ignore($user->id),
            ],
            'dateNaissance' => 'required|date_format:d/m/Y',
            'adresse' => 'required|string|max:100',
            'numero' => 'required|string|max:20',
            'club' => 'nullable|string|max:100',
            'npa' => 'required|string|max:10',
            'commune' => 'required|string|max:100',
            'nationalite' => 'required|string|max:100',
            'telephone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('Participant', 'telephone')->ignore($participant->id),
            ],
            'tailleTshirt' => 'required|string|in:XS,S,M,L,XL,XXL',
            'photo' => 'nullable|image|max:1024',
        ]);

        $user->update([
            'email' => $validated['email'],
        ]);

        $participantData = [
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'date_naissance' => Carbon::createFromFormat('d/m/Y', $validated['dateNaissance'])->format('Y-m-d'),
            'adresse' => trim($validated['adresse'].' '.$validated['numero']),
            'equipe_nom' => $validated['club'] ?? null,
            'code_postal' => $validated['npa'],
            'ville' => $validated['commune'],
            'pays' => $validated['nationalite'],
            'nationalite' => $validated['nationalite'],
            'telephone' => $validated['telephone'],
            'taille_tshirt' => $validated['tailleTshirt'],
        ];

        if ($request->hasFile('photo')) {
            $participantData['photo'] = file_get_contents($request->file('photo')->getRealPath());
        }

        $participant->update($participantData);

        return $this->show($request);
    }

    private function splitAdresseNumero(?string $adresse): array
    {
        if (!$adresse) {
            return ['', ''];
        }

        if (preg_match('/^(.*)\s+(\d+[A-Za-z]?)$/', trim($adresse), $matches)) {
            return [trim($matches[1]), $matches[2]];
        }

        return [$adresse, ''];
    }
}
