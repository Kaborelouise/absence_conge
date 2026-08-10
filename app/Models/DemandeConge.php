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
        'session_administrative_id',
        'statut',
        'date_debut',
        'date_fin',
        'date_effet',
                 ];

    protected $casts = [
        'lieu_jouissance' => 'array',
        'abandonnee' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function sessionAdministrative()
    {
        return $this->belongsTo(SessionAdministrative::class, 'session_administrative_id');
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

        return $user->role->libelle === 'Agent RH';
    }

    //un agent peut abandonner sa demande si elle n'est pas encore compilé
    public function peutEtreAbandonneePar(User $user): bool

    {
        if ($this->abandonnee || $this->estCompilee())
            {
                return false;
            }

            return $this->user_id === $user->id;
    }
}