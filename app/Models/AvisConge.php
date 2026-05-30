<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvisConge extends Model
{
    protected $fillable = [
        'avisconge', 'commentaire', 'type', 'demande_conge_id'
    ];

    public function demandeconge()
    {
        return $this->belongsTo(DemandeConge::class);
    }
                        
}
