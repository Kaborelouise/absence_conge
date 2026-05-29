<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JustificatifAbsence extends Model
{
    protected $fillable = [
        'fichier_path',
        'type',
        'demande_absence_id'
    ];

    public function demandeAbsence()
    {
        return $this->belongsTo(DemandeAbsence::class);
    }
}
