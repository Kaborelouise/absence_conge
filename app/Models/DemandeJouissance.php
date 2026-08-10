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
            return ['Agent RH', 'DG'];
        }

        if ($role === 'Agent RH') {
            return ['SG'];
        }

        // Cas du DG, RH puis PCA décide
        if ($role === 'DG') {
            return ['Agent RH', 'PCA'];
        }

        // Cas Responsable de direction, RH puis SG décide
        if ($role === 'Responsable Direction') {
            return ['Agent RH', 'SG'];
        }

        // Cas Agent de direction ou Chef de département :
        // RH puis Responsable de direction décide — INCHANGÉ
        if ($role === 'Chef de Département' || $user->est_responsable_departement) {
            return ['Agent RH', 'Responsable Direction'];
        }

        // Cas Agent simple d'un département :
        // Chef Dpt → RH → Responsable de direction — INCHANGÉ
        return ['Chef de Département', 'Agent RH', 'Responsable Direction'];
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

        $role     = $user->role->libelle;
        $prochain = $this->prochainActeur();

        if (in_array($role, ['SG', 'DG', 'PCA'])) {
            return $prochain === $role;
        }

        if ($role === 'Responsable Direction') {
            $dirUser  = $user->departement->direction_id ?? null;
            $dirAgent = $this->user->departement->direction_id ?? null;
            return $prochain === 'Responsable Direction'
                && $dirUser === $dirAgent;
        }

        if ($role === 'Chef de Département' || $user->est_responsable_departement) {
            return $prochain === 'Chef de Département'
                && $user->departement_id === $this->user->departement_id;
        }

        if ($role === 'Agent RH') {
            return $prochain === 'Agent RH';
        }

        return false;
    }
}