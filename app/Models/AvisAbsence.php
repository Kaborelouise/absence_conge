<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvisAbsence extends Model
{
    protected $fillable = ['avisabsence', 'type', 'commentaire', 'demande_absence_id'];

    public function demandeAbsence()
    {
        return $this->belongsTo(DemandeAbsence::class);
    }
}
