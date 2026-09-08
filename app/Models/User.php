<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;
use App\Models\Direction;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'password', 'matricule', 'nom', 'prenom', 'poste', 'email',
        'signature', 'est_responsable_departement', 'est_responsable_direction',
        'role_id', 'departement_id', 'solde_conge', 'solde_absence',
        'date_prise_service', 'certificat_prise_service', 'last_login_at', 'genre',
        'mot_de_passe_temporaire', 'mot_de_passe_expire_at', 'direction_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'           => 'datetime',
            'password'                    => 'hashed',
            'est_responsable_departement' => 'boolean',
            'est_responsable_direction'   => 'boolean',
            'date_prise_service'          => 'date',
            'last_login_at'               => 'datetime',
            'mot_de_passe_temporaire'     => 'boolean',
            'mot_de_passe_expire_at'      => 'datetime',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    public function direction()
    {
        return $this->belongsTo(Direction::class, 'direction_id');
    }

    public function directionReelle(): ?Direction
    {
        if ($this->departement_id) {
            return $this->departement?->direction;
        }
        return $this->direction;
    }

    public function demandeAbsences()
    {
        return $this->hasMany(DemandeAbsence::class, 'user_id');
    }

    public function demandeConges()
    {
        return $this->hasMany(DemandeConge::class, 'user_id');
    }

    public function demandeJouissances()
    {
        return $this->hasMany(DemandeJouissance::class, 'user_id');
    }

      public function prochainePeriodeConge(): ?array
        {
            if (!$this->date_prise_service) return null;

            $datePriseService = Carbon::parse($this->date_prise_service);
            $moisEcoules       = $datePriseService->diffInMonths(Carbon::now());
            $n                 = intdiv($moisEcoules, 11) + 1;

            $debutTravail  = $datePriseService->copy()->addMonthsNoOverflow(11 * ($n - 1));
            $finTravail    = $debutTravail->copy()->addMonthsNoOverflow(11)->subDay();
            $dateEffet     = $finTravail->copy()->addDay();
            $finJouissance = $dateEffet->copy()->addMonthNoOverflow()->subDay();

            return [
                'debut_travail'  => $debutTravail,
                'fin_travail'    => $finTravail,
                'date_effet'     => $dateEffet,
                'fin_jouissance' => $finJouissance,
            ];
        }

        public function peutSoumettreCongeMaintenant(): bool
        {
            $periode = $this->prochainePeriodeConge();
            if (!$periode) return false;

            return Carbon::now()->greaterThanOrEqualTo($periode['date_effet']);
        }

    public function periodeOuvrantDroit(): ?array
    {
        if (!$this->date_prise_service) return null;

        $debut = Carbon::parse($this->date_prise_service);
        $fin   = $debut->copy()->addMonthsNoOverflow(11)->subDay();   

        return ['debut' => $debut, 'fin' => $fin];
    }

  
    public function periodeTravail(): array
    {
        return $this->periodeOuvrantDroit() ?? ['debut' => null, 'fin' => null];
    }

    public function periodeTravailFormatee(): string
    {
        $periode = $this->periodeOuvrantDroit();
        if (!$periode) return '—';

        return $periode['debut']->format('d/m/Y') . ' au ' . $periode['fin']->format('d/m/Y');
    }

    public function datePeriodeJouissance(): ?Carbon
    {
        $periode = $this->periodeOuvrantDroit();
        if (!$periode) return null;

        return $periode['fin']->copy()->addDay();
    }

    public function periodeJouissance(): ?array
    {
        $debut = $this->datePeriodeJouissance();
        if (!$debut) return null;

        return [
            'debut' => $debut,
            'fin'   => $debut->copy()->addMonth()->subDay(),
        ];
    }

    public function periodeJouissanceFormatee(): string
    {
        $periode = $this->periodeJouissance();
        if (!$periode) return '—';

        return $periode['debut']->format('d/m/Y') . ' au ' . $periode['fin']->format('d/m/Y');
    }

    
    public function estEligibleAuConge(): bool
    {
        if (!$this->date_prise_service) return false;

        $mois = (int) Carbon::parse($this->date_prise_service)
            ->diffInMonths(Carbon::now());

        return $mois >= 11;
    }

    public function estEligible(): bool
    {
        return $this->estEligibleAuConge();
    }

    public function aOnzeMoisService(): bool
    {
        if (!$this->date_prise_service) return false;

        return (int) Carbon::parse($this->date_prise_service)
            ->diffInMonths(Carbon::now()) >= 11;
    }

    public function aUneDemandeCongeCompilee(?int $sessionId = null): bool
    {
        return $this->demandeConges()
            ->when($sessionId, fn($q) => $q->where('session_administrative_id', $sessionId))
            ->where('statut', 'compilee')
            ->exists();
    }

    
    public function estEligibleJouissance(): bool
    {
        return $this->aOnzeMoisService() && $this->aUneDemandeCongeCompilee();
    }

    public function periodeJouissanceFormatee_DEPRECATED_NOTUSED()
    {
        // méthode retirée volontairement — voir periodeJouissanceFormatee() ci-dessus
    }

    public function periodeTravailFormatee_ANCIENNE_NOTUSED()
    {
        // conservé pour référence uniquement, ne pas utiliser
    }

    public function interims()
    {
        return $this->hasMany(DemandeAbsence::class, 'interimaire_id');
    }


        public function fonctionAffichee(): string
    {
        $role = $this->role?->libelle;

        return match ($role) {
            'SG'  => 'Secrétaire Général',
            'DG'  => 'Directeur Général',
            'PCA' => "Président du Conseil d'Administration",
            default => $this->fonctionDepuisStructure(),
        };
    }
    private function fonctionDepuisStructure(): string
    {
        if ($this->est_responsable_direction || $this->role?->libelle === 'Responsable Direction') {
            $direction = $this->directionReelle();
            if ($direction && $direction->libelle_long) {
                return preg_replace('/^Direction\b/iu', 'Directeur', $direction->libelle_long);
            }
        }

        if ($this->est_responsable_departement || $this->role?->libelle === 'Chef de Departement') {
            $departement = $this->departement;
            if ($departement && $departement->libelle_long) {
                return preg_replace('/^Département\b/iu', 'Chef du Département', $departement->libelle_long);
            }
            return 'Chef de Département';
        }

        return $this->poste ?? 'Agent';
    }
}