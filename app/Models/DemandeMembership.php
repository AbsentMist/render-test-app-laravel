<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandeMembership extends Model
{
    protected $table = 'DemandeMembership';

    public $timestamps = false;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'adresse',
        'code_postal',
        'ville',
        'pays',
        'telephone',
        'date_naissance',
        'description',
        'status',
        'date_creation',
        'date_decision',
        'id_admin_decideur',
        'notes_admin',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_creation' => 'datetime',
        'date_decision' => 'datetime',
    ];

    public function adminDecideur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_admin_decideur');
    }
}
