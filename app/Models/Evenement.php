<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evenement extends Model
{
    use HasFactory;

    protected $table = 'Evenement';
    public $timestamps = false;

    protected $fillable = [
        'nom', 'logo', 'site', 'couleur_primaire', 'couleur_secondaire',
        'is_avertissement', 'is_document', 'is_questionnaire', 'is_rabais', 'is_actif', 'is_interne',
    ];

    protected $casts = [
        'is_avertissement' => 'boolean', 'is_document' => 'boolean',
        'is_questionnaire' => 'boolean', 'is_rabais' => 'boolean',
        'is_actif' => 'boolean', 'is_interne' => 'boolean',
    ];

    //Gestion du logo (conversion en base64) :
    protected $hidden = ['logo'];

    // Ajout d'un nouvel attribut 'logo_base64' qui sera calculé à partir du champ logo
    protected $appends = ['logo_base64'];

    // L'Accesseur : Laravel appelle cette fonction pour générer 'logo_base64'
    public function getLogoBase64Attribute()
    {
        if ($this->logo) {
            // Convertit le binaire en chaîne Base64 lisible par le navigateur HTML
            return 'data:image/jpeg;base64,' . base64_encode($this->logo);
        }
        return null;
    }
}