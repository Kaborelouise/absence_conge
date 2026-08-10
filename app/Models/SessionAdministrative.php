<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SessionAdministrative;
use App\Models\DemandeConge;
use App\Models\DemandeAbsence;
use App\Models\DemandeJouissance;

class SessionAdministrative extends Model
{
    protected $table = 'sessions_demandes';

    protected $fillable = [
        'libelle',
        'annee',
        'date_debut',
        'date_fin',
        'ouverte',
        'active_absence',
        'active_conge',
        'active_jouissance',
        'soldes_reinitialises',
        'created_by',

    ];

    protected $casts = [
        'libelle'        => 'string',
        'annee'            => 'integer',
        'date_debut'         => 'date',
        'date_fin'           => 'date',
        'ouverte'           => 'boolean',
        'active_absence'     => 'boolean',
        'active_conge'       => 'boolean',
        'active_jouissance'  => 'boolean',
        'soldes_reinitialises' => 'boolean',
    ];

    // indique que personne n'a créer la session administrative
    public function creePar()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // une session administrative possède plusieurs demandes d'absence
    public function demandeAbsences()
    {
        return $this->hasMany(DemandeAbsence::class, 'session_administrative_id');
    }

    // une session administrative possède plusieurs demandes de congé
    public function demandeConges()
    {
        return $this->hasMany(DemandeConge::class, 'session_administrative_id');
    }

    // une session administrative possède plusieurs demandes de jouissance
    public function demandeJouissances()
    {
        return $this->hasMany(DemandeJouissance::class, 'session_administrative_id');
    }


    //cherche la session admini courante 
    public static function courante(): ?self
    {
        $aujourdhui = \Carbon\Carbon::today();

        return self::where('ouverte', true)
            ->where('date_debut', '<=', $aujourdhui)
            ->where('date_fin', '>=', $aujourdhui)
            ->first();
    }

    //cette fonction répond a la question la session est ouverte ou pas, pour qu'on puisse savoir si on peut créer une nouvelle session ou pas pour les trois types de demandes
    public function estOuvertePour(string $type): bool
    {
        return match ($type) {
            'absence'    => $this->active_absence,
            'conge'      => $this->active_conge,
            'jouissance' => $this->active_jouissance,
            default      => false,
        };
    }

    //permet d'empêcher la création de 2 session qui couvrent la meme periode
    public static function chevaucheUneSessionExistante(
        \Carbon\Carbon $dateDebut, // carbon bibliothèque laravel utilise pour manipuler les date
        \Carbon\Carbon $dateFin,
        ?int $ignorerId = null
    ): bool {
        return self::where('date_debut', '<=', $dateFin)
            ->where('date_fin', '>=', $dateDebut)
            ->when($ignorerId !== null, fn ($q) => $q->where('id', '!=', $ignorerId))
            ->exists();
    }

    // ceete fonction répond a la question la session est ouverte ou pas, pour qu'on puisse savoir si on peut créer une nouvelle session ou pas
    public function estOuverte(): bool
       
        {
            return $this->ouverte;
        }

   


}