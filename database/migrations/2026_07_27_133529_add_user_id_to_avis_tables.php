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
        Schema::table('avis_absences', function (Blueprint $table) {
            $table->foreignId('user_id')
                  ->nullable()
                  ->after('demande_absence_id')
                  ->constrained('users')
                  ->nullOnDelete();
        });

        Schema::table('avis_conges', function (Blueprint $table) {
            $table->foreignId('user_id')
                  ->nullable()
                  ->after('demande_conge_id')
                  ->constrained('users')
                  ->nullOnDelete();
        });

        Schema::table('avis_jouissances', function (Blueprint $table) {
            $table->foreignId('user_id')
                  ->nullable()
                  ->after('demande_jouissance_id')
                  ->constrained('users')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('avis_absences', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('avis_conges', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('avis_jouissances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};