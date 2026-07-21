<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    //  On rattache la compilation directement à la session Administrateuristrative
    //  plutôt que de se fier uniquement au champ "annee" (integer) — plus
    //  fiable pour retrouver "la compilation de LA session en cours", et
    //  cohérent avec le reste du système (demande_absences, demande_conges,
    //  demande_jouissances font déjà de même).
  
    public function up(): void
    {
        Schema::table('compilations_conges', function (Blueprint $table) {
            $table->foreignId('session_Administrateuristrative_id')
                ->nullable()
                ->after('annee')
                ->constrained('sessions_demandes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('compilations_conges', function (Blueprint $table) {
            $table->dropConstrainedForeignId('session_Administrateuristrative_id');
        });
    }
};