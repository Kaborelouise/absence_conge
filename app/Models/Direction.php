<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Direction extends Model
{
<<<<<<< HEAD
    protected $fillable = ['nom_direction'];

    public function departements()
    {
        return $this->hasMany(Departement::class);
    }
=======
    //
>>>>>>> 1ce37f274bc27af71ef5858c73775e967614fd85
}
