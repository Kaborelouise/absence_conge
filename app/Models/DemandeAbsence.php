<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeAbsence extends Model
{
    protected $fillable = [
        'num_demande',
        'date_debut',
        'date_fin',
        'motif',
        'interimaire',
        'retenue_salaire',
        'statut',
        'user_id'
    ];

    // Les relations

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function justificatifAbsence()
    {
        return $this->hasOne(JustificatifAbsence::class);
    }

    public function avisAbsence()
    {
        return $this->hasMany(AvisAbsence::class);
    }

    // la logique du circuit

    public function circuitAttendu(): array
    {
        $user = $this->user; // l'utilisateur qui crée la demande(qui fait cette demande )
        $role = $user->role->libelle; // le rôle de l'utilisateur qui fait la demande

        if ($role === 'sg') 
            {
            return ['agent_rh', 'dg'];
            }

        if ($role === 'dg') {
            return ['agent_rh', 'pca'];
        }

        if ($role === 'responsable_direction') {
            return ['agent_rh', 'sg', 'dg'];
        }

        if ($role === 'chef_departement' || $user->est_responsable_departement) {
            return ['responsable_direction', 'agent_rh', 'sg', 'dg'];
        }

        return ['chef_departement', 'responsable_direction', 'agent_rh', 'sg', 'dg'];
    }

    public function prochainActeur(): ?string
    {
        $circuit = $this->circuitAttendu();

        $avisDejaGiven = $this->avisAbsence
            ->where('avis', 'favorable')
            ->pluck('type')
            ->toArray();

        foreach ($circuit as $etape) {
            if (!in_array($etape, $avisDejaGiven)) {
                return $etape;
            }
        }

        return null;
    }

    public function peutDonnerAvis(User $user): bool
    {
        if (in_array($this->statut, ['validee', 'rejette'])) {
            return false;
        }

        $role     = $user->role->libelle;
        $prochain = $this->prochainActeur();

        if (in_array($role, ['sg', 'dg', 'pca'])) {
            return $prochain === $role;
        }

        if ($role === 'responsable_direction') {
            $dirUser   = $user->departement->direction_id ?? null;
            $dirAgent  = $this->user->departement->direction_id ?? null;
            return $prochain === 'responsable_direction'
                && $dirUser === $dirAgent;
        }

        if ($role === 'chef_departement' || $user->est_responsable_departement) {
            return $prochain === 'chef_departement'
                && $user->departement_id === $this->user->departement_id;
        }

        if ($role === 'agent_rh') {
            return $prochain === 'agent_rh';
        }

        return false;
    }

    public function validateurFinal(): string
    {
        $jours = \Carbon\Carbon::parse($this->date_debut)
                               ->diffInDays($this->date_fin);
        return $jours > 5 ? 'dg' : 'sg';
    }
}