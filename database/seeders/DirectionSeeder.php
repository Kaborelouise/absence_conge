<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Direction;

class DirectionSeeder extends Seeder
{
    public function run(): void
    {
        Direction::firstOrCreate(
            ['libelle_court' => 'DSA'],
            ['libelle_long' => 'Direction des Systèmes Applicatifs']
        );

        Direction::firstOrCreate(
            ['libelle_court' => 'DIG'],
            ['libelle_long' => 'Direction Intranet Gouvernemental']
        );
    }
}