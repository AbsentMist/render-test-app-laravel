<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categorie extends Model
{
    protected $table = 'Categorie';
    public $timestamps = false;
    protected $fillable = ['nom', 'modele'];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'id_categorie');
    }

}