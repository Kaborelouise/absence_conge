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
            if (!Schema::hasColumn('demande_conges', 'date_debut')) {
                $table->date('date_debut')->nullable();
            }
            if (!Schema::hasColumn('demande_conges', 'date_fin')) {
                $table->date('date_fin')->nullable();
            }
            if (!Schema::hasColumn('demande_conges', 'date_effet')) {
                $table->date('date_effet')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demande_conges', function (Blueprint $table) {
            if (Schema::hasColumn('demande_conges', 'date_debut')) {
                $table->dropColumn('date_debut');
            }
            if (Schema::hasColumn('demande_conges', 'date_fin')) {
                $table->dropColumn('date_fin');
            }
            if (Schema::hasColumn('demande_conges', 'date_effet')) {
                $table->dropColumn('date_effet');
            }
        });
    }
};
