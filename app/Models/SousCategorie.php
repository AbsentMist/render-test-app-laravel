<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SousCategorie extends Model
{
    protected $table = 'SousCategorie';
    public $timestamps = false;
    protected $fillable = ['nom', 'modele'];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'id_sous_categorie');
    }

}