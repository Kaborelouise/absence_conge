<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
<<<<<<< HEAD
    protected $fillable = ['nom_departement'];

    public function utilisateurs() {
        return $this->belongTo(Direction::class);
    }
    
=======
    //
>>>>>>> 1ce37f274bc27af71ef5858c73775e967614fd85
}
