<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeConge extends Model
{
    protected $fillable = [
        'lieu_jouissance',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Une demande de congé peut avoir 0 ou 1 avis (compilation RH)
    public function avisConge()
    {
        return $this->hasOne(AvisConge::class);
    }

    public function estCompilee(): bool
    {
        return $this->avisConge !== null;
    }
    public function peutEtreCompileePar(User $user): bool
    {
        if ($this->estCompilee()) {
            return false;
        }

        return $user->role->libelle === 'agent_rh';
    }
}