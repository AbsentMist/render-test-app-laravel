<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OptionCochable extends Model
{
    protected $table = 'OptionCochable';
    public $timestamps = false;
    public $incrementing = false; 

    protected $fillable = ['id', 'is_coche'];
}