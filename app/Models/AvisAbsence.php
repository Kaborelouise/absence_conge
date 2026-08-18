<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvisAbsence extends Model
{
    protected $fillable = [
        'avis',
        'type',
        'commentaire',
        'demande_absence_id',
        'user_id',
    ];

    public function demandeAbsence()
    {
        return $this->belongsTo(DemandeAbsence::class);
    }

     public function user()                  // ← ajouté
    {
        return $this->belongsTo(User::class);
    }
}