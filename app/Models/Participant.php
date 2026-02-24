<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;
    protected $table = 'Participant';
public $timestamps = false;
protected $primaryKey = 'id';

protected $fillable = [
    'id_user', 'nom', 'prenom', 'date_naissance', 'equipe_nom',
    'adresse', 'code_postal', 'ville', 'pays', 'telephone',
    'nationalite', 'instagram', 'facebook', 'taille_tshirt', 'sexe', 'photo',
];

public function user()
{
    return $this->belongsTo(User::class, 'id_user', 'id');
}
}