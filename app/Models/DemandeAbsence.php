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
        'user_id',
    ];

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


    public function circuitAttendu(): array
    {
        $user  = $this->user;
        $role  = $user->role->libelle;

        // on calcul la durée pour déterminer le validateur final
        // diffInDays retourne un entier positif
        $jours = \Carbon\Carbon::parse($this->date_debut)
                               ->diffInDays($this->date_fin);
        $validateurFinal = $jours > 5 ? 'dg' : 'sg';

        // Cas du sg le DG valide toujours peu importe la durée
        if ($role === 'sg') {
            return ['agent_rh', 'dg'];
        }

        // Cas du dg cest le PCA qui valide toujours
        if ($role === 'dg') {
            return ['agent_rh', 'pca'];
        }

        // Cas du Responsable de direction 
        if ($role === 'responsable_direction') {
            return ['agent_rh', $validateurFinal];
        }

        // Cas Agent de direction ou Chef de département :
        if ($role === 'chef_departement' || $user->est_responsable_departement) {
            return ['responsable_direction', 'agent_rh', $validateurFinal];
        }

        // Cas Agent simple d'un département 

        return ['chef_departement', 'responsable_direction', 'agent_rh', $validateurFinal];
    }

    // Retourne le type d'avis attendu à l'étape actuelle.


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

    // cette fonction vérifie si l'utilisateur peut donner son avis
    public function peutDonnerAvis(User $user): bool
    {
        if (in_array($this->statut, ['validee', 'rejetee'])) {
            return false;
        }

        $role     = $user->role->libelle;
        $prochain = $this->prochainActeur();

        if (in_array($role, ['sg', 'dg', 'pca'])) {
            return $prochain === $role;
        }

        if ($role === 'responsable_direction') {
            $dirUser  = $user->departement->direction_id ?? null;
            $dirAgent = $this->user->departement->direction_id ?? null;
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
}