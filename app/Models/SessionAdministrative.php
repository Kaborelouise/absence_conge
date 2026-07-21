<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionAdministrative extends Model

{

    protected $table = 'sessions_demandes';

    protected $fillable = [
        'libelle',
        'annee',
        'date_debut',
        'date_fin',
        'active_absence',
        'active_conge',
        'active_jouissance',
        'created_by',
    ];

    protected $casts = [
        'date_debut'         => 'date',
        'date_fin'           => 'date',
        'active_absence'     => 'boolean',
        'active_conge'       => 'boolean',
        'active_jouissance'  => 'boolean',
    ];

    public function creePar()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * NOUVEAU : relations inverses, pour lister facilement toutes les
     * demandes rattachées à cette session (ex: dans un futur écran
     * d'historique par campagne).
     */
    public function demandeAbsences()
    {
        return $this->hasMany(DemandeAbsence::class, 'session_Administrative_id');
    }

    public function demandeConges()
    {
        return $this->hasMany(DemandeConge::class, 'session_Administrative_id');
    }

    public function demandeJouissances()
    {
        return $this->hasMany(DemandeJouissance::class, 'session_Administrative_id');
    }


    public static function courante(): ?self
    {
        $aujourdhui = \Carbon\Carbon::today();

        return self::where('date_debut', '<=', $aujourdhui)
            ->where('date_fin', '>=', $aujourdhui)
            ->first();
    }

    public function estOuvertePour(string $type): bool
    {
        return match ($type) {
            'absence'    => $this->active_absence,
            'conge'      => $this->active_conge,
            'jouissance' => $this->active_jouissance,
            default      => false,
        };
    }


    public static function chevaucheUneSessionExistante(
        \Carbon\Carbon $dateDebut,
        \Carbon\Carbon $dateFin,
        ?int $ignorerId = null
    ): bool {
        return self::where('date_debut', '<=', $dateFin)
            ->where('date_fin', '>=', $dateDebut)
            ->when($ignorerId !== null, fn ($q) => $q->where('id', '!=', $ignorerId))
            ->exists();
    }
}