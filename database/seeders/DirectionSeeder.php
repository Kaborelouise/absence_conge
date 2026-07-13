<?php

namespace Database\Seeders;

use App\Models\Direction;
use Illuminate\Database\Seeder;

class DirectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Direction::updateOrCreate(
            ['libelle_court' => 'DSA'],
            [
                'libelle_long' => 'Direction des Systèmes Applicatifs',
            ]
        );
    }
}