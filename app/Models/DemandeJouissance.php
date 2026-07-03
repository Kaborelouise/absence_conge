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
        ];

    protected $cast = [
        'abandonnee' => 'boolean',
        //pour la cloture on ajoute cast datetime
        'cloture_at' => 'datetime',
    ];

    public function estCloturee(): bool
    {
        return $this->cloturee_at !== null;
    }
    //vérifie si l'agent peut cloturee : la demande est validée, les 2 certificats ont été uploader

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

    /**
 * L'auteur peut abandonner sa demande seulement si
 * elle n'est pas encore validée, rejetée ou abandonnée.
 */
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