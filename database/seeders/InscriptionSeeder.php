<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Course;
use App\Models\Inscription;
use App\Models\Groupe;
use App\Models\User;

class InscriptionSeeder extends Seeder
{
    private function creerDossard(int $idInscription, int $idCourse): void
    {
        $course = Course::find($idCourse);
        if (!$course) return;

        $dernierNumero = DB::table('Dossard')
            ->join('Inscription', 'Dossard.id_inscription', '=', 'Inscription.id')
            ->where('Inscription.id_course', $idCourse)
            ->max('Dossard.numero');

        $numero = $dernierNumero ? $dernierNumero + 1 : $course->premier_dossard;
        if ($numero > $course->dernier_dossard) return;

        DB::table('Dossard')->insert([
            'id_inscription' => $idInscription,
            'numero'         => $numero,
        ]);
    }

    private function ajouterMembreGroupe(int $idGroupe, int $idParticipant, bool $estFondateur = false): void
    {
        DB::table('GroupeParticipant')->updateOrInsert(
            ['id_groupe' => $idGroupe, 'id_participant' => $idParticipant],
            ['statut' => $estFondateur ? 'fondateur' : 'membre']
        );
    }

    public function run(): void
    {
        $jd      = User::where('email', 'jeandani.guillerm@hes-so.ch')->first();
        $patrice = User::where('email', 'pb@runningeneva.ch')->first();
        $remi    = User::where('email', 'remi.perroud@hes-so.ch')->first();
        $steven  = User::where('email', 'steven.ngoie@hes-so.ch')->first();

        if (!$jd || !$patrice) return;

        // ===================================================
        // Inscription individuelle — 10km des Ponts (JD)
        // ===================================================
        $ponts10 = Course::where('nom', '10km des Ponts')->first();
        if ($ponts10 && $jd?->participant) {
            $insc = Inscription::updateOrCreate(
                ['id_participant' => $jd->participant->id, 'id_course' => $ponts10->id],
                [
                    'tarif'               => 25.00,
                    'status_paiement'     => 'Validé',
                    'montant_rabais'      => 0,
                    'avertissement_valide'=> true,
                    'date_paiement'       => now(),
                ]
            );
            $this->creerDossard($insc->id, $ponts10->id);
        }

        // ===================================================
        // Inscription individuelle — 5km Populaire (Patricia)
        // ===================================================
        $ponts5 = Course::where('nom', '5km Populaire')->first();
        if ($ponts5 && $patrice?->participant) {
            $insc = Inscription::updateOrCreate(
                ['id_participant' => $patrice->participant->id, 'id_course' => $ponts5->id],
                [
                    'tarif'               => 20.00,
                    'status_paiement'     => 'Validé',
                    'montant_rabais'      => 0,
                    'avertissement_valide'=> false,
                    'date_paiement'       => now(),
                ]
            );
            $this->creerDossard($insc->id, $ponts5->id);
        }

        // ===================================================
        // Inscription en groupe — 20km Groupe des Ponts
        // Groupe : JD + Patricia + Rémi + Steven
        // ===================================================
        $ponts20 = Course::where('nom', '20km Groupe des Ponts')->first();
        if ($ponts20 && $jd?->participant && $patrice?->participant && $remi?->participant && $steven?->participant) {
            $groupe = Groupe::updateOrCreate(
                ['nom' => 'Les Ponts Express', 'id_course' => $ponts20->id],
                ['type' => 'Groupe']
            );

            foreach ([$jd, $patrice, $remi, $steven] as $index => $user) {
                if (!$user?->participant) continue;
                $insc = Inscription::updateOrCreate(
                    ['id_participant' => $user->participant->id, 'id_course' => $ponts20->id],
                    [
                        'tarif'               => 30.00,
                        'status_paiement'     => 'Validé',
                        'montant_rabais'      => 0,
                        'avertissement_valide'=> true,
                        'id_groupe'           => $groupe->id,
                        'date_paiement'       => now(),
                    ]
                );
                $this->creerDossard($insc->id, $ponts20->id);
                $this->ajouterMembreGroupe($groupe->id, $user->participant->id, $index === 0);
            }
        }

        // ===================================================
        // Inscription avec rabais — Antigel Night Run (Rémi)
        // ===================================================
        $antigel = Course::where('nom', 'Antigel Night Run 10km')->first();
        if ($antigel && $remi?->participant) {
            $insc = Inscription::updateOrCreate(
                ['id_participant' => $remi->participant->id, 'id_course' => $antigel->id],
                [
                    'tarif'               => 30.00,
                    'status_paiement'     => 'Validé',
                    'montant_rabais'      => 10.00,
                    'avertissement_valide'=> true,
                    'date_paiement'       => now(),
                ]
            );
            $this->creerDossard($insc->id, $antigel->id);
        }

        // ===================================================
        // Inscription en relais — Nocturne Relais 2x5km
        // Relais : JD + Patricia
        // ===================================================
        $relais = Course::where('nom', 'Nocturne Relais 2x5km')->first();
        if ($relais && $jd?->participant && $patrice?->participant) {
            $groupeRelais = Groupe::updateOrCreate(
                ['nom' => 'Nuit Sauvage', 'id_course' => $relais->id],
                ['type' => 'Relais']
            );

            foreach ([$jd, $patrice] as $index => $user) {
                if (!$user?->participant) continue;
                $insc = Inscription::updateOrCreate(
                    ['id_participant' => $user->participant->id, 'id_course' => $relais->id],
                    [
                        'tarif'               => 45.00,
                        'status_paiement'     => 'Validé',
                        'montant_rabais'      => 0,
                        'avertissement_valide'=> true,
                        'id_groupe'           => $groupeRelais->id,
                        'date_paiement'       => now(),
                    ]
                );
                $this->creerDossard($insc->id, $relais->id);
                $this->ajouterMembreGroupe($groupeRelais->id, $user->participant->id, $index === 0);
            }
        }

        // ===================================================
        // Inscription challenge — Urban Trail de Genève (Steven)
        // ===================================================
        $trail = Course::where('nom', 'Urban Trail de Genève')->first();
        if ($trail && $steven?->participant) {
            $insc = Inscription::updateOrCreate(
                ['id_participant' => $steven->participant->id, 'id_course' => $trail->id],
                [
                    'tarif'               => 75.00,
                    'status_paiement'     => 'Validé',
                    'montant_rabais'      => 0,
                    'avertissement_valide'=> false,
                    'participe_challenge' => true,
                    'type_challenge'      => 'Entreprise',
                    'equipe_challenge'    => 'HES-SO',
                    'date_paiement'       => now(),
                ]
            );
            $this->creerDossard($insc->id, $trail->id);
        }

        // ===================================================
        // Inscription annulée — Urban Trail 15km (Rémi)
        // ===================================================
        $trail15 = Course::where('nom', 'Urban Trail 15km')->first();
        if ($trail15 && $remi?->participant) {
            Inscription::updateOrCreate(
                ['id_participant' => $remi->participant->id, 'id_course' => $trail15->id],
                [
                    'tarif'               => 45.00,
                    'status_paiement'     => 'Annulé',
                    'montant_rabais'      => 0,
                    'avertissement_valide'=> false,
                    'date_paiement'       => now(),
                ]
            );
        }
    }
}