<?php

/**
 * @fileoverview InscriptionsExport.php
 * @description Classe d'export des inscriptions au format Excel (xlsx) ou CSV.
 *              Implémente les interfaces Maatwebsite\Excel nécessaires pour :
 *              - la source de données (FromCollection)
 *              - les en-têtes de colonnes (WithHeadings)
 *              - le mapping des données (WithMapping)
 *              - le titre de la feuille (WithTitle)
 *              - le redimensionnement automatique des colonnes (ShouldAutoSize)
 *              - la configuration CSV (WithCustomCsvSettings : séparateur ";", BOM UTF-8)
 *              Reçoit une collection pré-filtrée depuis InscriptionController::exportAdmin().
 *              Supporte deux presets : 'logistique' (défaut) et 'banque'.
 * @author Ngoie Steven
 */

namespace App\Exports;

use App\Models\Inscription;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Illuminate\Support\Collection;

class InscriptionsExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithCustomCsvSettings
{
    /** Collection pré-filtrée transmise par le contrôleur — Neris Alessandro */
    private Collection $inscriptions;

    /**
     * Preset d'export : 'logistique' (défaut) ou 'banque'.
     * Détermine les colonnes et le format des données exportées.
     */
    private string $preset;

    /**
     * Reçoit la collection d'inscriptions déjà filtrées et triées,
     * ainsi que le preset déterminant le format d'export.
     *
     * @author Neris Alessandro
     * @param  Collection $inscriptions Inscriptions à exporter.
     * @param  string     $preset       Preset d'export : 'logistique' (défaut) ou 'banque'.
     */
    public function __construct(Collection $inscriptions, string $preset = 'logistique')
    {
        $this->inscriptions = $inscriptions;
        $this->preset = $preset;
    }

    /**
     * Retourne la collection source pour l'export.
     *
     * @author Ngoie Steven
     */
    public function collection()
    {
        return $this->inscriptions;
    }

    /**
     * Définit les en-têtes des colonnes du fichier exporté.
     * Les colonnes varient selon le preset sélectionné.
     *
     * @author Ngoie Steven
     * @return array Liste des libellés de colonnes.
     */
    public function headings(): array
    {
        if ($this->preset === 'banque') {
            return [
                'Id inscription',
                'Code participant',
                'Nom',
                'Prénom',
                'Adresse',
                'Code postal',
                'Ville',
                'Nationalité',
                'Événement',
                'Course',
                'Type',
                'Date paiement',
                'Statut paiement',
                'Montant brut (CHF)',
                'Montant rabais (CHF)',
                'Montant net (CHF)',
                'Dossard',
                'Groupe',
                'Type challenge',
                'Équipe challenge',
            ];
        }

        // Preset 'logistique' (défaut)
        return [
            'Dossard',
            'Nom',
            'Prénom',
            'Événement',
            'Course',
            'Date inscription',
            'Tarif (CHF)',
            'Statut',
            'Type',
            'Téléphone',
            'Code participant',
            'Groupe',
            'Équipe challenge',
            'Taille t-shirt',
            'Date de naissance',
        ];
    }

    /**
     * Mappe une inscription vers un tableau de valeurs pour chaque ligne du fichier.
     * Les valeurs nulles sont remplacées par "—" pour la lisibilité.
     * Le format des colonnes varie selon le preset sélectionné.
     *
     * @author Ngoie Steven
     * @author Neris Alessandro (ajout de la colonne Id)
     * @param  mixed $inscription Inscription ou tableau normalisé à mapper.
     * @return array Ligne de données correspondant aux en-têtes.
     */
    public function map($inscription): array
    {
        if ($this->preset === 'banque') {
            $montantBrut   = (float) ($inscription->tarif ?? 0);
            $montantRabais = (float) ($inscription->montant_rabais ?? 0);

            return [
                $inscription->id,
                $inscription->code_participant ?? '—',
                $inscription->participant->nom ?? '',
                $inscription->participant->prenom ?? '',
                $inscription->participant->adresse ?? '',
                $inscription->participant->code_postal ?? '',
                $inscription->participant->ville ?? '',
                $inscription->participant->nationalite ?? '',
                $inscription->course->evenement->nom ?? '',
                $inscription->course->nom ?? '',
                $inscription->course->type ?? '—',
                $inscription->date_paiement
                    ? \Carbon\Carbon::parse($inscription->date_paiement)->format('d/m/Y H:i:s')
                    : '—',
                $inscription->status_paiement ?? '—',
                number_format($montantBrut, 2, '.', ''),
                number_format($montantRabais, 2, '.', ''),
                number_format(max($montantBrut - $montantRabais, 0), 2, '.', ''),
                $inscription->dossard->numero ?? '—',
                $inscription->groupe->nom ?? '—',
                $inscription->type_challenge ?? '—',
                $inscription->equipe_challenge ?? '—',
            ];
        }

        // Preset 'logistique' (défaut)
        return [
            $inscription->dossard->numero ?? '—',
            $inscription->participant->nom ?? '',
            $inscription->participant->prenom ?? '',
            $inscription->course->evenement->nom ?? '',
            $inscription->course->nom ?? '',
            $inscription->date_paiement
                ? \Carbon\Carbon::parse($inscription->date_paiement)->format('d/m/Y H:i:s')
                : '—',
            $inscription->tarif ?? '0',
            $inscription->status_paiement ?? '—',
            $inscription->course->type ?? '—',
            $inscription->participant->telephone ?? '',
            $inscription->code_participant ?? '',
            $inscription->groupe->nom ?? '—',
            $inscription->equipe_challenge ?? '—',
            $inscription->participant->taille_tshirt ?? '',
            $inscription->participant->date_naissance
                ? \Carbon\Carbon::parse($inscription->participant->date_naissance)->format('d/m/Y')
                : '',
        ];
    }

    /**
     * Configuration CSV : séparateur ";" et BOM UTF-8 pour compatibilité Excel.
     *
     * @author Ngoie Steven
     * @return array Paramètres CSV.
     */
    public function getCsvSettings(): array
    {
        return [
            'delimiter'       => ';',
            'use_bom'         => true, // BOM nécessaire pour l'affichage correct des accents dans Excel
            'output_encoding' => 'UTF-8',
        ];
    }

    /**
     * Définit le nom de l'onglet dans le fichier Excel généré.
     *
     * @return string Titre de la feuille.
     */
    public function title(): string
    {
        return 'Inscriptions';
    }
}