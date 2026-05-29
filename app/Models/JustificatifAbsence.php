<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JustificatifAbsence extends Model
{
<<<<<<< HEAD
    protected $fillable = [
        'fichier_path',
        'type',
        'demande_absence_id'
    ];

    public function demandeAbsence()
    {
        return $this->belongsTo(DemandeAbsence::class);
    }
=======
    //
>>>>>>> 1ce37f274bc27af71ef5858c73775e967614fd85
}
