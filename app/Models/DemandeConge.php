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
        // AJOUTÉ : rattachement à la campagne annuelle.
        'session_Administrateuristrative_id',
        'statut',
                 ];

    protected $casts = [
        'lieu_jouissance' => 'array',
        'abandonnee' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function sessionAdministrateuristrative()
    {
        return $this->belongsTo(SessionAdministrateuristrative::class, 'session_Administrateuristrative_id');
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

        return $user->role->libelle === 'Agent RH';
    }

    //un Agent peut abandonner sa demande si elle n'est pas encore compilée
    public function peutEtreAbandonneePar(User $user): bool

    {
        if ($this->abandonnee || $this->estCompilee())
            {
                return false;
            }

            return $this->user_id === $user->id;
    }
}