<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeJouissance extends Model
{
    protected $fillable =[
        'num_demande',
        'date_debut',
        'date_fin',
        'nombre_jour',
        'utilisateur_id'];

        public function utilisateur() {return $this->belongsTo(Utilisateur::class); }
        public function avis() { return $this->hasMany(AvisJouissance::class); }
}
