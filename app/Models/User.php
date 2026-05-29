<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'matricule', 'nom', 'prenom', 'poste', 'email', 'signature', 'password', 'password', 'est_responsable_departement', 'est_responsable_direction', 'role_id', 'departement_id', 'direction_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'est_responsable_departement' => 'boolean',
            'est_responsable_direction' => 'boolean',
        ];

    }

    //Relations 
    public function role () { return $this->belongsTo(Role::class); }
    Public function departement() { return $this->belongsTo(Departement::class); }
    Public function direction() { return $this->belongsTo(Direction::class); }
    Public function demandeAbsences() { return $this->hasMany(DemandeAbsence::class); }
    Public function demandeConges() { return $this->hasMany(DemandeConge::class); }
    Public function demandeJouissance() { return $this->hasMany(DemandeJouissance::class); }

}
