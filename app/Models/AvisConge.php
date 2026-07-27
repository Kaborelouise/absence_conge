<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvisConge extends Model
{
    protected $fillable = [
        'avis',
        'type',
        'commentaire',
        'demande_conge_id',
    ];

    public function demandeConge()
    {
        return $this->belongsTo(DemandeConge::class);
    }
        public function user()
    {
        return $this->belongsTo(User::class);
    }
}