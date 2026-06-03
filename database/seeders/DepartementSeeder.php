<?php

namespace Database\Seeders;
use App\Models\Direction;
use Illuminate\Database\Seeder;
use App\Models\Departement;

class DepartementSeeder extends Seeder
{
    public function run(): void
    {
        $direction = Direction::first(); // récupère la direction déjà créée

        Departement::create([
            'libelle_court' => 'DRH',
            'libelle_long' => 'Direction des Ressources Humaines',
            'direction_id' => $direction->id
        ]);
    }
}