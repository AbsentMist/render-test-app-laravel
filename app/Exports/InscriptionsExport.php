<?php

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
    private Collection $inscriptions;

    public function __construct(Collection $inscriptions)
    {
        $this->inscriptions = $inscriptions;
    }

    public function collection()
    {
        return $this->inscriptions;
    }

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
            'Type'
        ];
    }

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
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',
            'use_bom' => true, 
            'output_encoding' => 'UTF-8',
        ];
    }
}