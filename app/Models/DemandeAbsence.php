<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeAbsence extends Model
{
    //
    protected $fillable = [
        'num_demande', 
        'date_debut', 
        'date_fin', 
        'motif', 
        'interimaire',
        'retenue_salaire', 
        'statut',
        'user_id'
    ];
     //demande fait par un utilisateur(user)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');

    }

    //Une demande peut avoir 0 ou 1 justificatif
    public function justificatifabsence()
    {
        return $this->hasOne(Justificatifabsence::class);
    }
    
    //une demande peut avoir plusieurs avis
    public function avisabsence()
    {
        return $this->hasMany(avisabsence::class);
    }


}
