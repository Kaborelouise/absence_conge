<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
        {
            Schema::table('demande_conges', function (Blueprint $table) {
                $table->dropColumn('lieu_jouissance');
            });

            Schema::table('demande_conges', function (Blueprint $table) {
                // CORRECTION : nullable() pour éviter l'erreur sur données existantes
                $table->json('lieu_jouissance')->nullable()->after('id');
            });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demande_conges', function (Blueprint $table) {
            $table->enum('lieu_jouissance', [
                'Burkina faso',
                'Afrique',
                'Canada',
                'Asie',
                'Amerique',
                'Europe',
            ])->after('id');
        });
    }
};
