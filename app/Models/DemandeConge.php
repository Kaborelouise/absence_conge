<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeConge extends Model
{
        protected $fillable = [
            'num_demande',
            'lieu_jouissance',
            'user_id',
            'abandonnee',
            ];

    protected $casts = [
        'lieu_jouissance' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Une demande de congé peut avoir 0 ou 1 avis 
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

    //un agent peut abandonner sa demande si elle n'est pas encore compilée
    public function peutEtreAbandonneePar(User $user): bool

    {
        if ($this->abandonnee || $this->estCompilee())
            {
                return false;
            }

            return $this->user_id === $user->id;
    }
}