<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SousCategorie extends Model
{
    protected $table = 'SousCategorie';
    public $timestamps = false;
    protected $fillable = ['nom'];

    //Gestion des relations aux prochains sprints
}