<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvisJouissance extends Model
{ 
    protected $fillable = [
        'avis',
        'type',
        'commentaire',
        'demande_jouissance_id'
    ];

     public function demandeJouissance()
     {
        return $this->belongsTo(DemandeJouissance::class);
     }
}
