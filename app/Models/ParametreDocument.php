<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParametreDocument extends Model
{
    protected $table = 'parametres_documents';

    protected $fillable = [
        'ministere_libelle',
        'sigle_ministere',
        'sigle_secretariat',
        'sigle_agence',
        'nb_chiffres_decision',
        'nb_chiffres_cessation',
        'nb_chiffres_prise_service',
        'nb_chiffres_interim',
    ];

    public static function actuel(): self
    {
        return self::firstOrCreate([]);
    }
}