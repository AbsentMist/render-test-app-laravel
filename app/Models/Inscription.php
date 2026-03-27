<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inscription extends Model
{
    
    protected $table = 'Inscription';
    
    
    public $timestamps = false;

    
    protected $fillable = [
        'id_participant',
        'id_course',
        'id_groupe',
        'id_document',
        'code_participant',
        'tarif',
        'status_paiement',
        'montant_rabais',
        'avertissement_valide',
        'date_paiement',
    ];

     // RELATIONS (BELONGS TO - "Appartient à")

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'id_course');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'id_participant');
    }

    public function groupe(): BelongsTo
    {
        return $this->belongsTo(Groupe::class, 'id_groupe');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'id_document');
    }


    public function dossard(): HasOne
    {
        // Une inscription est liée à un seul dossard
        return $this->hasOne(Dossard::class, 'id_inscription');
    }

    public function resultat(): HasOne
    {
        // Une inscription a un seul résultat
        return $this->hasOne(Resultat::class, 'id_inscription');
    }

    public function reponsesQuestions(): HasMany
    {
        // Une inscription peut avoir plusieurs réponses
        return $this->hasMany(ReponseQuestion::class, 'id_inscription');
    }
}