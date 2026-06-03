<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Direction;

class DirectionSeeder extends Seeder
{
    public function run(): void
    {
       Direction::create([
            'libelle_court' => 'DSA',
            'libelle_long' => 'Direction des Systèmes Applicatifs'
        ]);
    }
}