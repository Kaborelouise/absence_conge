<?php

namespace Database\Seeders;

use App\Models\Departement;
use App\Models\Direction;
use Illuminate\Database\Seeder;

class DepartementSeeder extends Seeder
{
    public function run(): void
    {
        $direction = Direction::first();

        Departement::firstOrCreate(
            ['libelle_court' => 'INFO'],
            [
                'libelle_long' => 'Informatique',
                'direction_id' => $direction->id,
            ]
        );
    }
}