<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeOrganisation extends Model
{
    protected $table = 'ChallengeOrganisation';
    public $timestamps = false;

    protected $fillable = ['id_course', 'nom', 'type'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'id_course');
    }
}
