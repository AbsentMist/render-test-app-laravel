<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $table = 'Document';

    public $timestamps = false;

    protected $fillable = [
        'url',
        'date_debut',
        'date_fin',
        'valable',
        'id_participant',
        'id_inscription'
    ];

    /**
     * Le participant propriétaire du document
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'id_participant');
    }

    /**
     * L'inscription associée au document
     */
    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class, 'id_inscription');
    }
}
