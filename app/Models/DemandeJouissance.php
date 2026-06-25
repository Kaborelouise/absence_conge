<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeJouissance extends Model
{
    protected $fillable = [
        'num_demande',
        'date_debut',
        'date_fin',
        'nombre_jour',
        'statut',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function avis()
    {
        return $this->hasMany(AvisJouissance::class);
    }

    public function circuitAttendu(): array
    {
        $user = $this->user;
        $role = $user->role->libelle;

        // Cas du sg d'abord RH vérifie puis DG décide 
        if ($role === 'sg') {
            return ['agent_rh', 'dg'];
        }

        // Cas du dg, RH puis PCA décide
        if ($role === 'dg') {
            return ['agent_rh', 'pca'];
        }

        // Cas Responsable de direction, RH puis SG décide
        if ($role === 'responsable_direction') {
            return ['agent_rh', 'sg'];
        }

        // Cas Agent de direction ou Chef de département :
        // RH puis Responsable de direction décide — INCHANGÉ
        if ($role === 'chef_departement' || $user->est_responsable_departement) {
            return ['agent_rh', 'responsable_direction'];
        }

        // Cas Agent simple d'un département :
        // Chef Dpt → RH → Responsable de direction — INCHANGÉ
        return ['chef_departement', 'agent_rh', 'responsable_direction'];
    }

    public function prochainActeur(): ?string
    {
        $circuit = $this->circuitAttendu();

        $avisDejaGiven = $this->avis
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

    // Vérifie si l'utilisateur connecté peut donner son avis
  
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