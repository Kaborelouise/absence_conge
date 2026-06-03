<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
     public function run(): void
    {
        $this->call([
            DirectionSeeder::class,   // 1
            DepartementSeeder::class, // 2
            RoleSeeder::class,        // 3
                 // 4 (optionnel)
        ]);
    }
}