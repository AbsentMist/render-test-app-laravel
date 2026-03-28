<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChoixOption extends Model
{
    // On précise le nom de la table
    protected $table = 'ChoixOption';
    
    public $timestamps = false;

    // Puisque c'est une table pivot avec une clé primaire composée, 
    // on désactive l'auto-incrément
    public $incrementing = false;
    protected $primaryKey = ['id_inscription', 'id_option'];

    protected $fillable = [
        'id_option', 
        'id_inscription', 
        'quantite'
    ];

    /**
     * L'option choisie (T-shirt, Médaille, Repas, etc.)
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class, 'id_option');
    }

    /**
     * L'inscription à laquelle ce choix est rattaché
     */
    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class, 'id_inscription');
    }
}