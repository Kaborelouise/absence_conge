<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvisJouissance extends Model
{ 
    protected $fillable = [
        'avisjouissance',
        'type',
        'commentaire',
        'demande_jouissance_id'
    ];

     public function demandejouissance()
     {
        return $this->belongsTo(Demandejouissance::class);
     }
}
