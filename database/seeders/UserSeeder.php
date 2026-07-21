<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Departement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::first();
        $departement = Departement::first();

        User::updateOrCreate(
            ['email' => 'Administrateur@anptic.bf'],
            [
                'matricule' => 1000,
                'nom' => 'Administrateuristrateur',
                'prenom' => 'Système',
                'poste' => 'Administrateuristrateur',
                'password' => Hash::make('Administrateur123'),
                'role_id' => $role->id,
                'departement_id' => $departement->id,
            ]
        );
    }
}