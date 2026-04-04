<?php

namespace App\Exports;

use App\Models\Inscription;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class InscriptionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithCustomCsvSettings
{
    public function collection()
    {
        // On récupère toutes les inscriptions avec les relations nécessaires
        return Inscription::with(['course.evenement', 'participant', 'dossard'])->orderBy('date_paiement', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Dossard',
            'Nom',
            'Prénom',
            'Événement',
            'Course',
            'Date inscription',
            'Tarif (CHF)',
            'Status',
            'Type'
        ];
    }

    public function map($inscription): array
    {
        return [
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
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',
            'use_bom' => true, 
            'output_encoding' => 'UTF-8',
        ];
    }
}