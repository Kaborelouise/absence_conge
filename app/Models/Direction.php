<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Direction extends Model
{
    protected $fillable = ['libelle_coute', 'libelle_long'];

    public function departements()
    {
        return $this->hasMany(Departement::class);
    }
}
