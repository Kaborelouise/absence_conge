<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Direction;
use App\Models\Departement;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // RÔLES
        
        Role::firstOrCreate(['libelle' => 'agent']);
        Role::firstOrCreate(['libelle' => 'chef_departement']);     
        Role::firstOrCreate(['libelle' => 'responsable_direction']);
        Role::firstOrCreate(['libelle' => 'agent_rh']);           
        Role::firstOrCreate(['libelle' => 'sg']);                
        Role::firstOrCreate(['libelle' => 'dg']);                  
        Role::firstOrCreate(['libelle' => 'pca']);                   
        Role::firstOrCreate(['libelle' => 'admin']);              

        // DIRECTION PAR DÉFAUT
        // Nécessaire car departement_id est obligatoire
        // dans la table users
        $direction = Direction::firstOrCreate(
            ['libelle_court' => 'DG'],
            ['libelle_long'  => 'Direction Générale']
        );

        // DÉPARTEMENT PAR DÉFAUT
        // Nécessaire car departement_id = 1 dans le controller
        // d'inscription
        Departement::firstOrCreate(
            ['libelle_court' => 'DRH'],
            [
                'libelle_long' => 'Département Ressources Humaines',
                'direction_id' => $direction->id
            ]
        );
    }
}