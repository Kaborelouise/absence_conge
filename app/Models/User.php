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
     protected $table = 'users';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */



    protected $fillable = ['password', 'matricule', 'nom', 'prenom', 'poste', 'email', 'signature', 'est_responsable_departement', 'est_responsable_direction', 'role_id',  'departemnt_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'est_responsable_departement' => 'boolean',
            'est_responsable_direction' => 'boolean',
        ];}

        
    // Un utilisateur a un rôle
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Un utilisateur appartient à un département
    
    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    // Demandes de l'utilisateur
    public function demandeAbsences()
    {
        return $this->hasMany(DemandeAbsence::class, 'user_id');
        // On précise 'utilisateur_id' car Laravel chercherait 'user_id' par défaut
    }

    public function demandeConges()
    {
        return $this->hasMany(DemandeConge::class, 'user_id');
    }

    public function demandeJouissances()
    {
        return $this->hasMany(DemandeJouissance::class, 'user_id');
    }

}