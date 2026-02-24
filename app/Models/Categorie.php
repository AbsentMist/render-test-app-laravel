<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    protected $table = 'Categorie';
    public $timestamps = false;
    protected $fillable = ['nom'];

    //Gestion des relations aux prochains sprints
}