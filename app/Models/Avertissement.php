<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Avertissement extends Model
{
    protected $table = 'Avertissement';
    public $timestamps = false;
    protected $fillable = ['titre', 'contenu', 'modele'];

    //Gestion des relations aux prochains sprints
}