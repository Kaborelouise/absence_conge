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
        'user_id'];

        public function user() 
        {
            return $this->belongsTo(User::class, 'user_id'); 
        }
        public function avis() 
        {
             return $this->hasMany(AvisJouissance::class); 
        }
}
