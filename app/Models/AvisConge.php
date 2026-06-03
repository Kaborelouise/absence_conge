<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DemandeConge;

class AvisConge extends Model
{
    protected $fillable = [
        'avis', 'commentaire', 'type', 'demande_conge_id'
    ];

    public function demandeConge()
    {
        return $this->belongsTo(DemandeConge::class);
    }
                        
}
