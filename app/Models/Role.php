<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['libelle'];

    //un role est attribué a plusieurs utilisateurs
    public function utilisateurs()
    {
        return $this->hasMany(User::class);
    }

}
