<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'Administrateur',
            'Responsable Direction',
            'Responsable Département',
            'Agent',
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['libelle' => $role]
            );
        }
    }
}