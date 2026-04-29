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

    public function getPhotoAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }

        if (is_string($value) && str_starts_with($value, 'data:')) {
            return $value;
        }

        if (is_string($value) && base64_decode($value, true) !== false) {
            return 'data:image/jpeg;base64,' . $value;
        }

        return 'data:image/jpeg;base64,' . base64_encode($value);
    }

    public function setPhotoAttribute($value): void
    {
        if (is_string($value) && str_starts_with($value, 'data:')) {
            $commaPosition = strpos($value, ',');
            $decoded = $commaPosition === false ? null : base64_decode(substr($value, $commaPosition + 1), true);
            $this->attributes['photo'] = $decoded !== false && $decoded !== null ? $decoded : $value;
            return;
        }

        $this->attributes['photo'] = $value;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function groupes()
    {
        return $this->belongsToMany(Groupe::class, 'GroupeParticipant', 'id_participant', 'id_groupe')
                    ->withPivot('statut');
    }
}