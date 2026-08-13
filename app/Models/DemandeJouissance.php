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
        'abandonnee',
        'certificat_cessation',
        'certificat_prise_service',
        'cloturee_at',
        'session_administrative_id',
    ];

    protected $casts = [
        'abandonnee' => 'boolean',
        'cloturee_at' => 'datetime',
    ];

    public function nombreJours(): int
    {
        return \Carbon\Carbon::parse($this->date_debut)
            ->diffInDays(\Carbon\Carbon::parse($this->date_fin)) + 1;
    }

    public function estCloturee(): bool
    {
        return $this->cloturee_at !== null;
    }
    //vérifie si l'Agent peut cloturee : la demande est validée, les 2 certificats ont été uploader

    public function peutEtreClotureePar(User $user): bool 
    { 
        return $this->statut === 'validee'
        && $this->certificat_cessation !== null
        && $this->certificat_prise_service !== null
        && !$this->estCloturee()
        && $this->user_id === $user->id;

    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

  
    public function sessionAdministrative()
    {
        return $this->belongsTo(SessionAdministrative::class, 'session_administrative_id');
    }

    public function avis()
    {
        return $this->hasMany(AvisJouissance::class);
    }

    public function circuitAttendu(): array
    {
        $user = $this->user;
        $role = $user->role->libelle;

        // Cas du SG d'abord RH vérifie puis DG décide 
        if ($role === 'SG') {
            return ['agent_rh', 'dg'];
        }

        if ($role === 'Agent RH') {
            return ['sg'];
        }

        // Cas du DG, RH puis PCA décide
        if ($role === 'DG') {
            return ['agent_rh', 'pca'];
        }

        // Cas Responsable de direction, RH puis SG décide
        if ($role === 'Responsable Direction') {
            return ['agent_rh', 'sg'];
        }

        // Cas Agent de direction ou Chef de département :
        // RH puis Responsable de direction décide — INCHANGÉ
        if ($role === 'Chef de Département' || $user->est_responsable_departement) {
            return ['agent_rh', 'responsable_direction'];
        }

        // Cas Agent simple d'un département 
        return ['chef_departement', 'agent_rh', 'responsable_direction'];
    }


     public function peutEtreAbandonneePar(User $user): bool
    {
          // Si déjà abandonnée
          if ($this->abandonnee ?? false) {
           return false;
        }

          // Si déjà terminée
        if (in_array($this->statut, ['validee', 'rejetee'])) {
        return false;
    }

    // Seulement l'auteur peut abandonner
      return $this->user_id === $user->id;
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

        if ($user->id === $this->user_id) {
            return false;
        }

        $role     = $user->role->libelle;
        $prochain = $this->prochainActeur();

        if ($prochain === null) {
            return false;
        }

        $etapeDejaTraitee = $this->avis->where('type', $prochain)->isNotEmpty();
        if ($etapeDejaTraitee) {
            return false;
        }

        if (in_array($role, ['SG', 'DG', 'PCA'])) {
            return $prochain === strtolower($role);
        }

        if ($role === 'Responsable Direction') {
            $dirUser  = $user->departement->direction_id ?? null;
            $dirAgent = $this->user->departement->direction_id ?? null;
            return $prochain === 'responsable_direction'
                && $dirUser !== null && $dirUser === $dirAgent;
        }

        if ($role === 'Chef de Département' || $user->est_responsable_departement) {
            return $prochain === 'chef_departement'
                && $user->departement_id === $this->user->departement_id;
        }

        if ($role === 'Agent RH') {
            return $prochain === 'agent_rh';
        }

        return false;
    }
}