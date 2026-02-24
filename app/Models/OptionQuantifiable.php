<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OptionQuantifiable extends Model
{
    protected $table = 'OptionQuantifiable';
    public $timestamps = false;
    
    
    public $incrementing = false; 
    
    protected $fillable = ['id', 'quantiteMin', 'quantiteMax'];
}