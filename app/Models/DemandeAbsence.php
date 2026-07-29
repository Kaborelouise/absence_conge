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
    ];

    protected $casts = [
        'abandonnee'      => 'boolean',
        'retenue_salaire' => 'boolean',
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

    // ================================================================
    // CORRECTION COMPLÈTE : circuit basé sur la POSITION RÉELLE
    // de l'agent dans la hiérarchie, pas seulement son rôle libellé
    //
    // Logique :
    // - Agent simple          → Chef Dpt → Resp. Direction → RH → SG/DG
    // - Chef de département   → Resp. Direction → RH → SG/DG
    // - Resp. de direction    → RH → SG/DG
    // - SG                    → RH → DG
    // - DG                    → RH → PCA
    //
    // Le validateur final (SG ou DG) dépend de la durée :
    //   ≤ 5 jours → SG
    //   > 5 jours → DG
    // ================================================================
    public function circuitAttendu(): array
    {
        $user  = $this->user;
        $role  = $user->role->libelle;
        $jours = $this->nombreJours();

        $validateurFinal = $jours > 5 ? 'dg' : 'sg';

        // SG → validé uniquement par DG (peu importe la durée)
        if ($role === 'SG') {
            return ['agent_rh', 'dg'];
        }

        // DG → validé uniquement par PCA
        if ($role === 'DG') {
            return ['agent_rh', 'pca'];
        }

        // Responsable de direction (rôle explicite)
        // → saute chef département ET responsable direction
        if ($role === 'Responsable Direction') {
            return ['agent_rh', $validateurFinal];
        }

        // Chef de département :
        // - soit rôle explicite "Responsable Département"
        // - soit est_responsable_departement = true (agent promu chef)
        // → saute l'étape chef département, commence à responsable direction
        if ($role === 'Responsable Département' || $user->est_responsable_departement) {
            return ['responsable_direction', 'agent_rh', $validateurFinal];
        }

        // Agent simple → circuit complet
        return ['chef_departement', 'responsable_direction', 'agent_rh', $validateurFinal];
    }

    // ================================================================
    // Retourne la prochaine étape du circuit
    // = première étape sans avis favorable
    // ================================================================
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

    // ================================================================
    // CORRECTION : peutDonnerAvis vérifie 4 conditions :
    // 1. La demande n'est pas terminée
    // 2. C'est le tour de cet utilisateur dans le circuit
    // 3. L'utilisateur n'a PAS déjà donné son avis
    // 4. L'utilisateur est bien dans la bonne direction/département
    // ================================================================
    public function peutDonnerAvis(User $user): bool
    {
        // Condition 1 : demande non terminée
        if (in_array($this->statut, ['validee', 'rejetee', 'abandonnee'])) {
            return false;
        }

        $role     = $user->role->libelle;
        $prochain = $this->prochainActeur();

        // Détermine le type d'avis que cet utilisateur pourrait donner
        $typeAvis = match(true) {
            $role === 'Responsable Département' || $user->est_responsable_departement => 'chef_departement',
            $role === 'Responsable Direction'                                          => 'responsable_direction',
            $role === 'Agent RH'                                                       => 'agent_rh',
            $role === 'SG'                                                             => 'sg',
            $role === 'DG'                                                             => 'dg',
            $role === 'PCA'                                                            => 'pca',
            default                                                                    => null,
        };

        if ($typeAvis === null) return false;

        // Condition 2 : c'est bien son tour
        if ($prochain !== $typeAvis) return false;

        // Condition 3 : il n'a pas déjà donné son avis
        $aDejaGive = $this->avisAbsence
            ->where('user_id', $user->id)
            ->isNotEmpty();

        if ($aDejaGive) return false;

        // Condition 4 : vérification périmètre géographique
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
        return $this->user_id === $user->id;
    }
}