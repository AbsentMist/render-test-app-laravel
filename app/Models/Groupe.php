<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Course;

class Groupe extends Model
{
    use HasFactory;

    protected $table = 'Groupe'; 
    public $timestamps = false;

    protected $fillable = [
    'nom',
    'type',
    'code_entreprise',
    'id_course', // nouveau pour permettre utilier même nom de groupe pour des courses différentes mais pas dans la même course
    ];

    protected static function boot()
    {
        parent::boot();

        //Forcer le type "Groupe" pour les groupes de type "Relais"
        static::creating(function ($groupe) {
            if (strtolower($groupe->type) === 'relais') {
                $groupe->type = 'Groupe';
            }
        });
    }

    //Relation avec les participants
    public function participants()
    {
        return $this->belongsToMany(Participant::class, 'GroupeParticipant', 'id_groupe', 'id_participant')
                    ->withPivot('statut'); 
    }

    public function course(): BelongsTo
{
    return $this->belongsTo(Course::class, 'id_course');
}
}