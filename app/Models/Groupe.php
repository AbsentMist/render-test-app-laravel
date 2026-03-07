<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Groupe extends Model
{
    use HasFactory;

    protected $table = 'Groupe'; 
    public $timestamps = false;

    protected $fillable = [
        'nom',
        'code_entreprise',
        'type' //ENUM dans la DB
    ];

    //Relation avec les participants
    public function participants()
    {
        return $this->belongsToMany(Participant::class, 'GroupeParticipant', 'id_groupe', 'id_participant')
                    ->withPivot('statut'); 
    }
}