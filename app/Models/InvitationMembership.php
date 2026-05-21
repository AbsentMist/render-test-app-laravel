<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InvitationMembership extends Model
{
    protected $table = 'InvitationMembership';

    public $timestamps = false;

    protected $fillable = [
        'id_user_participant',
        'id_admin_createur',
        'commentaire_admin',
        'status',
        'date_invitation',
        'date_annulation',
        'id_admin_annulation',
        'commentaire_annulation',
    ];

    protected $casts = [
        'date_invitation' => 'datetime',
        'date_annulation' => 'datetime',
    ];

    public function participantUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_participant');
    }

    public function adminCreateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_admin_createur');
    }

    public function adminAnnulation(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_admin_annulation');
    }

    public function formulaire(): HasOne
    {
        return $this->hasOne(FormulaireMembership::class, 'id_invitation');
    }
}