<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $table = 'Template';
    public $timestamps = false;
    protected $fillable = ['nom', 'contenu'];

}