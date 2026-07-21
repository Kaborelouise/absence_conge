<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    protected $fillable = ['libelle_court', 'libelle_long', 'direction_id'];

    //Un departement appartient a une seule direction
    public function direction()
    {
        return $this->belongsTo(Direction::class);
    }
    
    //un departement peut contenir plusieurs utilisateurs
    public function user()
    {
        return $this->hasMany(User::class);
    }

    
    public function users()
    {
        return $this->hasMany(User::class);
    }

}
