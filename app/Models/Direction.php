<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Direction extends Model
{
    protected $fillable = ['libelle_court', 'libelle_long'];

    public function departements()
    {
        return $this->hasMany(Departement::class);
    }

    public function responsable(): ?User
    {
        return User::where('direction_id', $this->id)
            ->where(function ($q) {
                $q->where('est_responsable_direction', true)
                  ->orWhereHas('role', fn ($q2) => $q2->where('libelle', 'Responsable Direction'));
            })
            ->first();
    }
}
