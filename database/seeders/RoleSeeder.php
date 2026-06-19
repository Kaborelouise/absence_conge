<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['libelle' => 'agent']);
        Role::firstOrCreate(['libelle' => 'chef_departement']);
        Role::firstOrCreate(['libelle' => 'responsable_direction']);
        Role::firstOrCreate(['libelle' => 'agent_rh']);
        Role::firstOrCreate(['libelle' => 'sg']);
        Role::firstOrCreate(['libelle' => 'dg']);
        Role::firstOrCreate(['libelle' => 'pca']);
        Role::firstOrCreate(['libelle' => 'admin']);
    }
}