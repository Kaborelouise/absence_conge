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

    //On ajoute le user_id pour savoir qui a donné l'avis
    'user_id',
    ];

    public function demandeAbsence()
    {
        return $this->belongsTo(DemandeAbsence::class);
    }

    //relation avec l'utilisateur qui a donné l'avis
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
