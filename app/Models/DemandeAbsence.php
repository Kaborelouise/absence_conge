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
        'abandonnee',
        // AJOUTÉ : rattachement à la campagne annuelle (voir SessionAdministrateuristrative).
        'session_Administrateuristrative_id',
        ];

        protected $cast =[
            'abandonnee' => 'boolean',
            'retenue_salaire' => 'boolean',

        ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function sessionAdministrateuristrative()
    {
        return $this->belongsTo(SessionAdministrateuristrative::class, 'session_Administrateuristrative_id');
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
        return \Carbon\Carbon::parse($this->date_debut)
            ->diffInDays(\Carbon\Carbon::parse($this->date_fin)) + 1;
    }

    public function circuitAttendu(): array
    {
        $user  = $this->user;
        $role  = $user->role->libelle;
        $jours = $this->nombreJours();

        // Cas du SG le DG valide toujours peu importe la durée
        // (règle spécifique confirmée : le SG ne peut pas s'auto-valider, donc même
        // une demande d'1 jour passe systématiquement par le DG. Pas de branchement
        // par durée pour ce cas, contrairement au circuit général ci-dessous.)
        if ($role === 'SG') {
            return ['Agent RH', 'DG'];
        }

        // Cas de l'Agent RH : il occupe normalement l'étape "Agent RH" (vérification)
        // du circuit des autres demandeurs, mais il ne peut pas vérifier sa propre
        // demande. Sa demande saute donc directement à l'avis du SG, sans notion de
        // durée (contrairement aux rôles ci-dessous où une courte absence de 1-2
        // jours ne nécessite aucune validation SG/DG).
        if ($role === 'Agent RH') {
            return ['SG'];
        }

        // Cas du DG cest le PCA qui valide toujours (même logique que ci-dessus)
        if ($role === 'DG') {
            return ['Agent RH', 'PCA'];
        }


        if ($jours <= 2) {
            $etapesFinales = [];
        } elseif ($jours < 5) {
            $etapesFinales = ['SG'];
        } else {
            $etapesFinales = ['DG'];
        }

        // Cas du Responsable de direction 
        if ($role === 'Responsable Direction') {
            return array_merge(['Agent RH'], $etapesFinales);
        }

        // Cas Agent de direction ou Chef de département :
        if ($role === 'Chef Département' || $user->est_responsable_departement) {
            return array_merge(['Responsable Direction', 'Agent RH'], $etapesFinales);
        }

        // Cas Agent simple d'un département 
        return array_merge(['Chef Département', 'Responsable Direction', 'Agent RH'], $etapesFinales);
    }
    // Retourne le type d'avis attendu à l'étape actuelle
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
        if (in_array($this->statut, ['validee', 'rejetee', 'abandonnee'])) {
            return false;
        }

        $role = $user->role->libelle;
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

        if (
            $role === 'Chef de Département'
            || $user->est_responsable_departement
        ) {

            return $prochain === 'Responsable Département'
                && $user->departement_id === $this->user->departement_id;
        }

        if ($role === 'Agent RH') {
            return $prochain === 'Agent RH';
        }

        return false;
    }
    //l'auteur peut abandonner sa demande si elle n'est pas encore traiter
      public function peutEtreAbandonneePar(User $user): bool
    {
    if ($this->abandonnee ?? false) {
        return false;
    }

    if (in_array($this->statut, ['validee', 'rejetee'])) {
        return false;
    }

    return $this->user_id === $user->id;
     }
    
}