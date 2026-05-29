<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    protected $fillable = ['nom_departement'];

    public function utilisateurs() {
        return $this->belongTo(Direction::class);
    }
    
}
