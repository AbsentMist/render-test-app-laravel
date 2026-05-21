<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormulaireMembership extends Model
{
    protected $table = 'FormulaireMembership';

    public $timestamps = false;

    protected $fillable = [
        'id_invitation',
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
        'prix',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_creation' => 'datetime',
        'date_decision' => 'datetime',
        'prix' => 'decimal:2',
    ];

    public function adminDecideur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_admin_decideur');
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(InvitationMembership::class, 'id_invitation');
    }
}