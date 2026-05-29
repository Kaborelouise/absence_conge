<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
<<<<<<< HEAD
    protected $fillable = ['libelle'];

    //un role peut etre attribué a plusieurs utilisateurs
    public function utilisateurs()
    {
        return $this->hasMany(User::class);
    }
=======
    //
>>>>>>> 1ce37f274bc27af71ef5858c73775e967614fd85
}
