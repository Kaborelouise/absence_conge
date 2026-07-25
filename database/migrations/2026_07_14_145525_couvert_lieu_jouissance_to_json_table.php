<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
   
    public function up(): void
    {

        DB::statement('ALTER TABLE demande_conges DROP CONSTRAINT IF EXISTS demande_conges_lieu_jouissance_check');
        DB::statement("
            ALTER TABLE demande_conges
            ALTER COLUMN lieu_jouissance TYPE json
            USING CASE
                WHEN lieu_jouissance IS NULL THEN '[]'::json
                ELSE to_json(ARRAY[lieu_jouissance]::text[])
            END
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE demande_conges
            ALTER COLUMN lieu_jouissance TYPE varchar(255)
            USING (lieu_jouissance->>0)
        ");
    }
};