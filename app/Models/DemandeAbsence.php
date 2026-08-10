<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DemandeAbsence extends Model
{
    protected $fillable = [
        'num_demande', 'date_debut', 'date_fin', 'motif', 'motif_autre',
        'interimaire', 'retenue_salaire', 'statut', 'user_id', 'abandonnee',
        'session_administrative_id',

        'cloturee_at',
    ];

    protected $casts = [
        'abandonnee'      => 'boolean',
        'retenue_salaire' => 'boolean',
        'cloturee_at'     => 'datetime',
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

    public function nombreJours(): int
    {
        return Carbon::parse($this->date_debut)
            ->diffInDays(Carbon::parse($this->date_fin)) + 1;
    }

    public function estCloturee(): bool
    {
        return $this->cloturee_at !== null;
    }

    // seul l'agent initiateur peut clôturer, et seulement une fois la
    // demande validée (par le SG ou le DG selon la durée) — pas avant,
    // pas s'il l'a déjà fait.
    public function peutEtreClotureePar(User $user): bool
    {
        return $this->statut === 'validee'
            && !$this->estCloturee()
            && $this->user_id === $user->id;
    }

  
    public function circuitAttendu(): array
    {
        $user  = $this->user;
        $role  = $user->role->libelle;
        $jours = $this->nombreJours();

        // DG est toujours validé par PCA
        if ($role === 'DG') {
            return ['agent_rh', 'pca'];
        }

        // SG → toujours validé par DG
        if ($role === 'SG') {
            return ['agent_rh', 'dg'];
        }

        if ($role === 'Agent RH') {
            return ['sg'];
        }

      
        $validateurFinal = match (true) {
            $jours < 2  => null,   
            $jours <= 5 => 'sg',
            default     => 'dg',
        };


        $etapesFinales = array_values(array_filter(['agent_rh', $validateurFinal]));


        if ($role === 'Responsable Direction') {
            return $etapesFinales;
        }


        if ($role === 'Chef de Département' || $user->est_responsable_departement) {
            return array_merge(['responsable_direction'], $etapesFinales);
        }

        // Agent simple → circuit complet
        return array_merge(['chef_departement', 'responsable_direction'], $etapesFinales);
    }

    // Retourne la prochaine étape du circuit = première étape sans avis favorable
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
        // Condition 1 : demande non terminée
        if (in_array($this->statut, ['validee', 'rejetee', 'abandonnee'])) {
            return false;
        }

        if ($user->id === $this->user_id) {
            return false;
        }

        $role     = $user->role->libelle;
        $prochain = $this->prochainActeur();

        // Détermine le type d'avis que cet utilisateur pourrait donner
        $typeAvis = match (true) {
            $role === 'Chef de Département' || $user->est_responsable_departement => 'chef_departement',
            $role === 'Responsable Direction'                                     => 'responsable_direction',
            $role === 'Agent RH'                                                  => 'agent_rh',
            $role === 'SG'                                                        => 'sg',
            $role === 'DG'                                                        => 'dg',
            $role === 'PCA'                                                       => 'pca',
            default                                                               => null,
        };

        if ($typeAvis === null) return false;

        if ($prochain !== $typeAvis) return false;


        $etapeDejaTraitee = $this->avisAbsence
            ->where('type', $typeAvis)
            ->isNotEmpty();

        if ($etapeDejaTraitee) return false;


        // Le chef de département ne peut agir que sur son propre département
        if ($typeAvis === 'chef_departement') {
            return $user->departement_id === $this->user->departement_id;
        }

        // Le responsable de direction ne peut agir que sur sa propre direction
        if ($typeAvis === 'responsable_direction') {
            $dirUser  = $user->departement->direction_id ?? null;
            $dirAgent = $this->user->departement->direction_id ?? null;
            return $dirUser !== null && $dirUser === $dirAgent;
        }

        // Agent RH, SG, DG, PCA → portée globale (toute l'organisation)
        return true;
    }

    public function peutEtreAbandonneePar(User $user): bool
    {
        if ($this->abandonnee ?? false) return false;
        if (in_array($this->statut, ['validee', 'rejetee'])) return false;
        if ($this->avisAbsence->isEmpty()) return false;
        return $this->user_id === $user->id;
    }
}