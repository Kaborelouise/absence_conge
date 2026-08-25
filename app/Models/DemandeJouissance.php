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

    // Verifie si l'Agent peut cloturer : la demande est validee, les 2 certificats ont ete uploades
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

public function responsableDirection(): ?User
{
    $directionId = $this->user?->departement?->direction_id;

    if (!$directionId) {
        return null;
    }

    return User::where(function ($q) {
        $q->where('est_responsable_direction', true)
          ->orWhereHas('role', fn ($q2) => $q2->where('libelle', 'Responsable Direction'));
    })
    ->whereHas('departement', function ($query) use ($directionId) {
        $query->where('direction_id', $directionId);
    })
    ->first();
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

        // Cas du SG : d'abord RH verifie, puis DG decide
        if ($role === 'SG') {
            return ['agent_rh', 'dg'];
        }

        if ($role === 'Agent RH') {
            return ['sg'];
        }

        // Cas du DG : RH puis PCA decide
        if ($role === 'DG') {
            return ['agent_rh', 'pca'];
        }

        // Cas Responsable de direction : RH puis SG decide
        if ($role === 'Responsable Direction') {
            return ['agent_rh', 'sg'];
        }

        // Cas Agent de direction ou Chef de departement : RH puis Responsable de direction decide
        if ($role === 'Chef de Departement' || $user->est_responsable_departement) {
            return ['agent_rh', 'responsable_direction'];
        }

        // Cas Agent simple d'un departement
        return ['chef_departement', 'agent_rh', 'responsable_direction'];
    }

    public function peutEtreAbandonneePar(User $user): bool
    {
        // Si deja abandonnee
        if ($this->abandonnee ?? false) {
            return false;
        }

        // Si deja terminee
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

    // Verifie si l'utilisateur connecte peut donner son avis
    public function peutDonnerAvis(User $user): bool
    {
        if (in_array($this->statut, ['validee', 'rejetee'])) {
            return false;
        }

        if ($user->id === $this->user_id) {
            return false;
        }

        $role = $user->role->libelle;
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
            $dirUser = $user->departement->direction_id ?? null;
            $dirAgent = $this->user->departement->direction_id ?? null;
            return $prochain === 'responsable_direction'
                && $dirUser !== null && $dirUser === $dirAgent;
        }

        if ($role === 'Chef de Departement' || $user->est_responsable_departement) {
            return $prochain === 'chef_departement'
                && $user->departement_id === $this->user->departement_id;
        }

        if ($role === 'Agent RH') {
            return $prochain === 'agent_rh';
        }

        return false;
    }

      
}