<?php

namespace Database\Seeders;

use App\Models\Direction;
use App\Models\Departement;
use Illuminate\Database\Seeder;

class DepartementSeeder extends Seeder
{
    public function run(): void
    {
        $dsa = Direction::where('libelle_court', 'DSA')->first();
        $daf = Direction::where('libelle_court', 'DIG')->first();

        Departement::firstOrCreate(
            ['libelle_court' => 'DEV'],
            [
                'libelle_long' => 'Département Développement',
                'direction_id' => $dsa->id,
            ]
        );

        Departement::firstOrCreate(
            ['libelle_court' => 'DRH'],
            [
                'libelle_long' => 'Département Ressources Humaines',
                'direction_id' => $daf->id,
            ]
        );
    }
}