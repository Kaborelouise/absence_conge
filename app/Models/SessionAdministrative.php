<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionAdministrative extends Model
{
    // Le nom de table par défaut qu'Eloquent devinerait à partir du nom du
    // modèle ("session_administratives") ne correspond pas au nom réel de la
    // table, qui est "sessions_demandes" (voir la migration
    // create_sessions_demandes_table — le nom du FICHIER de migration a pu
    // changer, mais c'est le Schema::create() à l'intérieur qui fait foi).
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
        return $this->hasMany(DemandeAbsence::class, 'session_administrative_id');
    }

    public function demandeConges()
    {
        return $this->hasMany(DemandeConge::class, 'session_administrative_id');
    }

    public function demandeJouissances()
    {
        return $this->hasMany(DemandeJouissance::class, 'session_administrative_id');
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