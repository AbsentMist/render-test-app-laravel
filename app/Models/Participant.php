<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;
    protected $table = 'Participant';
    protected $fillable = [
        'user_id',
        'nom',
        'prenom',
        'date_naissance',
        'equipe_nom',
        'adresse',
        'code_postal',
        'ville',
        'pays',
        'telephone',
        'nationalite',
        'taille_tshirt',
        'sexe',
        'photo',
    ];

    // Relation vers User
    // Remplace belongsTo par :
public function user()
{
    return $this->belongsTo(User::class, 'id', 'id');
}
}