<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'matricule', 'nom', 'prenom', 'poste', 'email', 'signature', 'password', 'password', 'est_responsable_departement', 'est_Responsable Direction', 'role_id', 'departement_id', 'direction_id', 'date_prise_service', 'certificat_prise_service'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
     protected $table = 'users';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */

    



    protected $fillable = ['password', 'matricule', 'nom', 'prenom', 'poste', 'email', 'signature', 'est_responsable_departement', 'est_Responsable Direction', 'role_id',  'departement_id',
        // AJOUTÉ : les deux nouveaux champs du cycle congé/jouissance (voir migration
        // 2026_07_08_000001_add_date_prise_service_to_users_table).
        'date_prise_service',
        'certificat_prise_service',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'est_responsable_departement' => 'boolean',
            'est_Responsable Direction' => 'boolean',
            // AJOUTÉ : cast en Carbon pour pouvoir faire des calculs de dates dessus
            // directement (ex: $user->date_prise_service->addMonths(11)).
            'date_prise_service' => 'date',
        ];}

        
    // Un utilisateur a un rôle
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Un utilisateur appartient à un département
    
    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    // Demandes de l'utilisateur
    public function demandeAbsences()
    {
        return $this->hasMany(DemandeAbsence::class, 'user_id');
        // On précise 'utilisateur_id' car Laravel chercherait 'user_id' par défaut
    }

    public function demandeConges()
    {
        return $this->hasMany(DemandeConge::class, 'user_id');
    }

    public function demandeJouissances()
    {
        return $this->hasMany(DemandeJouissance::class, 'user_id');
    }

    /**
     * NOUVEAU : calcule la "période ouvrant droit au congé" du cycle EN COURS,
     * c'est-à-dire les 11 mois de travail effectif exigés avant de pouvoir
     * prétendre au congé Administrateuristratif.
     *
     * Le cycle se répète chaque année à l'anniversaire de date_prise_service.
     * On calcule donc d'abord combien de cycles complets de 12 mois se sont
     * écoulés depuis date_prise_service, pour trouver le début du cycle actuel,
     * puis on applique la formule confirmée par le document officiel (décret) :
     * "+ 11 mois - 1 jour" pour la fin de la période ouvrant droit.
     *
     * Exemple (cas réel du décret) : date_prise_service = 21/12/2025
     * → période ouvrant droit = 21/12/2025 → 20/11/2026 (11 mois pile,
     *   bornes incluses).
     *
     * Retourne un tableau ['debut' => Carbon, 'fin' => Carbon].
     */
    public function periodeOuvrantDroit(): ?array
    {
        if ($this->date_prise_service === null) {
            return null;
        }

        $debutCycleActuel = $this->debutCycleActuel();

        return [
            'debut' => $debutCycleActuel->copy(),
            'fin'   => $debutCycleActuel->copy()->addMonths(11)->subDay(),
        ];
    }

    /**
     * NOUVEAU : calcule la "période de jouissance" du cycle EN COURS (le 12e
     * mois), pendant laquelle l'Agent peut effectivement poser ses jours de
     * congé. Commence le lendemain de la fin de la période ouvrant droit.
     *
     * Exemple (cas réel du décret) : date_prise_service = 21/12/2025
     * → période de jouissance = 21/11/2026 → 20/12/2026.
     */
    public function periodeJouissance(): ?array
    {
        if ($this->date_prise_service === null) {
            return null;
        }

        $debutCycleActuel = $this->debutCycleActuel();

        return [
            'debut' => $debutCycleActuel->copy()->addMonths(11),
            'fin'   => $debutCycleActuel->copy()->addMonths(12)->subDay(),
        ];
    }

   
    private function debutCycleActuel(): \Carbon\Carbon
    {
        $debut = $this->date_prise_service->copy();
        $cyclesEcoules = $debut->diffInMonths(\Carbon\Carbon::today()->startOfDay(), false);
        $cyclesEcoules = intdiv(max(0, $cyclesEcoules), 12);

        return $debut->copy()->addMonths($cyclesEcoules * 12);
    }

    /**
     * NOUVEAU : l'Agent est-il éligible au congé Administrateuristratif AUJOURD'HUI,
     * c'est-à-dire a-t-il dépassé la fin de sa période ouvrant droit du cycle
     * en cours (donc entré dans sa période de jouissance) ?
     * Utilisé pour bloquer la création d'une DemandeConge (voir
     * DemandeCongeController::store()).
     */
    public function estEligibleAuConge(): bool
    {
        $periode = $this->periodeOuvrantDroit();

        if ($periode === null) {
            // Pas de date_prise_service renseignée : on considère l'Agent comme
            // non éligible par sécurité (empêche de créer une demande de congé
            // tant que sa fiche n'est pas complète).
            return false;
        }

        return \Carbon\Carbon::today()->gt($periode['fin']);
    }

}