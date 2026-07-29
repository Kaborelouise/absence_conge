<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'password', 'matricule', 'nom', 'prenom', 'poste', 'email',
        'signature', 'est_responsable_departement', 'est_responsable_direction',
        'role_id', 'departement_id', 'solde_conge', 'solde_absence',
        'date_prise_service', 'certificat_prise_service', 'last_login_at',
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

   
    public function estEligibleAuConge(): bool
    {
        if (!$this->date_prise_service) return false;

        // CORRECTION : (int) force la conversion proprement
        $mois = (int) Carbon::parse($this->date_prise_service)
            ->diffInMonths(Carbon::now());

        return $mois >= 11;
    }

   
    public function datePeriodeJouissance(): ?Carbon
    {
        if (!$this->date_prise_service) return null;
        return Carbon::parse($this->date_prise_service)->addMonths(12);
    }

   
    public function estEligibleJouissance(): bool
    {
        if (!$this->date_prise_service) return false;

        $mois = (int) Carbon::parse($this->date_prise_service)
            ->diffInMonths(Carbon::now());

        if ($mois < 12) return false;

        return $this->demandeConges()
            ->where('statut', 'compilee')
            ->exists();
    }

    public function periodeTravailFormatee(): string
    {
        if (!$this->date_prise_service) return '—';

        $debut = Carbon::parse($this->date_prise_service);
        $fin   = $debut->copy()->addMonths(11)->subDay();

        return $debut->format('d/m/Y') . ' au ' . $fin->format('d/m/Y');
    }

    public function periodeJouissanceFormatee(): string
    {
        if (!$this->date_prise_service) return '—';

        $debut = Carbon::parse($this->date_prise_service)->addMonths(12);
        $fin   = $debut->copy()->addDays(30);

        return $debut->format('d/m/Y') . ' au ' . $fin->format('d/m/Y');
    }

    public function periodeOuvrantDroit(): ?array
    {
        if (!$this->date_prise_service) return null;

        $debut = Carbon::parse($this->date_prise_service);
        $fin   = $debut->copy()->addMonths(11)->subDay();

        return ['debut' => $debut, 'fin' => $fin];
    }

        public function periodeJouissance(): ?array
        {
            $debut = $this->datePeriodeJouissance();
            if (!$debut) return null;
            return [
                'debut' => $debut,
                'fin'   => $debut->copy()->addDays(30)
            ];
        }
}