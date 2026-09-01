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
        'interimaire_id',
        'numero_cessation_service',
        'numero_prise_service',
        'numero_interim',
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
        $directionId = $this->user?->directionReelle()?->id;

        if (!$directionId) {
            return null;
        }

        return User::where(function ($q) {
            $q->where('est_responsable_direction', true)
            ->orWhereHas('role', fn ($q2) => $q2->where('libelle', 'Responsable Direction'));
        })
        ->where(function ($q) use ($directionId) {
            $q->where('direction_id', $directionId)
            ->orWhereHas('departement', fn ($q2) => $q2->where('direction_id', $directionId));
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

        // Agent simple SANS département (rattaché directement à une direction) :
        // on saute chef_departement, qui n'a pas de sens ici
        if ($user->departement_id === null) {
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

    public function acteursPourEtape(string $etape): \Illuminate\Support\Collection
    {
        $agent = $this->user;

        return match ($etape) {
            'chef_departement' => \App\Models\User::where('departement_id', $agent->departement_id)
                ->where('id', '!=', $agent->id)
                ->where(function ($q) {
                    $q->where('est_responsable_departement', true)
                    ->orWhereHas('role', fn ($q2) => $q2->where('libelle', 'Chef de Département'));
                })
                ->get(),

            'responsable_direction' => \App\Models\User::whereHas('departement', function ($q) use ($agent) {
                    $q->where('direction_id', $agent->departement->direction_id ?? null);
                })
                ->where('id', '!=', $agent->id)
                ->where(function ($q) {
                    $q->where('est_responsable_direction', true)
                    ->orWhereHas('role', fn ($q2) => $q2->where('libelle', 'Responsable Direction'));
                })
                ->get(),

            'agent_rh' => \App\Models\User::whereHas('role', fn ($q) => $q->where('libelle', 'Agent RH'))->get(),
            'sg'       => \App\Models\User::whereHas('role', fn ($q) => $q->where('libelle', 'SG'))->get(),
            'dg'       => \App\Models\User::whereHas('role', fn ($q) => $q->where('libelle', 'DG'))->get(),
            'pca'      => \App\Models\User::whereHas('role', fn ($q) => $q->where('libelle', 'PCA'))->get(),

            default => collect(),
        };
    }

        public function notifierProchainActeur(string $notificationClass): void
    {
        $etape = $this->prochainActeur();
        if ($etape === null) return;

        $acteurs = $this->acteursPourEtape($etape);
        if ($acteurs->isEmpty()) return;

        \Illuminate\Support\Facades\Notification::send($acteurs, new $notificationClass($this));
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

        public function interimaire()
    {
        return $this->belongsTo(User::class, 'interimaire_id');
    }

    public function necessiteNoteInterim(): bool
    {
        if (!$this->interimaire_id) {
            return false;
        }

        $role = $this->user->role->libelle;

        return $role === 'Responsable Direction' || $this->user->est_responsable_direction
            || $role === 'Chef de Département'   || $this->user->est_responsable_departement
            || $role === 'Agent RH'
            || $role === 'SG'
            || $role === 'DG';
        // Agent simple et PCA -> jamais de note d'intérim
    }     

    public function signataireUser(): ?User
{
    $owner = $this->user;
    $role  = $owner->role->libelle;

    if ($role === 'DG') {
        return User::whereHas('role', fn ($q) => $q->where('libelle', 'PCA'))->first();
    }

    if ($role === 'SG') {
        return User::whereHas('role', fn ($q) => $q->where('libelle', 'DG'))->first();
    }

    if ($role === 'Responsable Direction' || $owner->est_responsable_direction || $role === 'Agent RH') {
        return User::whereHas('role', fn ($q) => $q->where('libelle', 'SG'))->first();
    }

    if ($role === 'Chef de Département' || $owner->est_responsable_departement) {
        $directionId = $owner->departement->direction_id ?? null;

        return User::where(function ($q) {
                $q->where('est_responsable_direction', true)
                  ->orWhereHas('role', fn ($q2) => $q2->where('libelle', 'Responsable Direction'));
            })
            ->whereHas('departement', fn ($q) => $q->where('direction_id', $directionId))
            ->first();
    }

    return null;
}
}