<?php

/**
 * @fileoverview InscriptionsExport.php
 * @description Classe d'export des inscriptions au format Excel (xlsx) ou CSV.
 *              Implémente les interfaces Maatwebsite\Excel nécessaires pour :
 *              - la source de données (FromCollection)
 *              - les en-têtes de colonnes (WithHeadings)
 *              - le mapping des données (WithMapping)
 *              - le redimensionnement automatique des colonnes (ShouldAutoSize)
 *              - la configuration CSV (WithCustomCsvSettings : séparateur ";", BOM UTF-8)
 *              Reçoit une collection pré-filtrée depuis InscriptionController::exportAdmin().
 * @author Ngoie Steven
 */

namespace App\Exports;

use App\Models\Inscription;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Illuminate\Support\Collection;

class InscriptionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithCustomCsvSettings
{
    /** Collection pré-filtrée transmise par le contrôleur — Neris Alessandro */
    private Collection $inscriptions;

    /**
     * Reçoit la collection d'inscriptions déjà filtrées et triées.
     * @author Neris Alessandro
     * @param  Collection $inscriptions Inscriptions à exporter.
     */
    public function __construct(Collection $inscriptions)
    {
        $this->inscriptions = $inscriptions;
    }

    /**
     * Retourne la collection source pour l'export.
     * @author Ngoie Steven
     */
    public function collection()
    {
        return $this->inscriptions;
    }

    /**
     * Définit les en-têtes des colonnes du fichier exporté.
     * @author Ngoie Steven
     * @return array Liste des libellés de colonnes.
     */
    public function headings(): array
    {
        return [
            'Id',
            'Dossard',
            'Nom',
            'Prénom',
            'Événement',
            'Course',
            'Date inscription',
            'Tarif (CHF)',
            'Status',
            'Type',
        ];
    }

    /**
     * Mappe une inscription vers un tableau de valeurs pour chaque ligne du fichier.
     * Les valeurs nulles sont remplacées par "—" pour la lisibilité.
     * @author Ngoie Steven
     * @author Neris Alessandro (ajout de la colonne Id)
     * @param  mixed $inscription Inscription ou tableau normalisé à mapper.
     * @return array Ligne de données correspondant aux en-têtes.
     */
    public function map($inscription): array
    {
        return [
            $inscription->id,
            $inscription->dossard->numero ?? '—',
            $inscription->participant->nom ?? '',
            $inscription->participant->prenom ?? '',
            $inscription->course->evenement->nom ?? '',
            $inscription->course->nom ?? '',
            $inscription->date_paiement ? substr($inscription->date_paiement, 0, 10) : '—',
            $inscription->tarif ?? '0',
            $inscription->status_paiement ?? '—',
            $inscription->course->type ?? '—',
        ];
    }

    /**
     * Configuration CSV : séparateur ";" et BOM UTF-8 pour compatibilité Excel.
     * @author Ngoie Steven
     * @return array Paramètres CSV.
     */
    public function getCsvSettings(): array
    {
        return [
            'delimiter'       => ';',
            'use_bom'         => true,        // BOM nécessaire pour l'affichage correct des accents dans Excel
            'output_encoding' => 'UTF-8',
        ];
    }
}