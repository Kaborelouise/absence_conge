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

   
    public function peutEtreAbandonneePar(User $user): bool
    {
        if ($this->abandonnee || $this->estCompilee()) {
            return false;
        }

        return $this->user_id === $user->id
            || in_array($user->role->libelle, ['Agent RH', 'Administrateur']);
    }
}