<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeConge extends Model
{
    protected $fillable = ['lieu_jouissance', 'statut', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function avisconge()
    {
        return $this->hasMany(AvisConge::class);
    }
}
