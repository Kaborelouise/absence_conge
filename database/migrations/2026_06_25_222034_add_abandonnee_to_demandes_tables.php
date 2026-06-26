<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajout abandonnee sur demande_absences
        Schema::table('demande_absences', function (Blueprint $table) {
            $table->boolean('abandonnee')->default(false)->after('statut');
        });

        // Ajout abandonnee sur demande_jouissances
        Schema::table('demande_jouissances', function (Blueprint $table) {
            $table->boolean('abandonnee')->default(false)->after('statut');
        });

        // Ajout statut sur demande_conges
        Schema::table('demande_conges', function (Blueprint $table) {
            $table->enum('statut', ['en_attente', 'compilee', 'rejetee'])
                  ->default('en_attente')
                  ->after('lieu_jouissance');
            $table->boolean('abandonnee')->default(false)->after('statut');
        });
    }

    public function down(): void
    {
        Schema::table('demande_absences', function (Blueprint $table) {
            $table->dropColumn('abandonnee');
        });

        Schema::table('demande_jouissances', function (Blueprint $table) {
            $table->dropColumn('abandonnee');
        });

        Schema::table('demande_conges', function (Blueprint $table) {
            $table->dropColumn(['statut', 'abandonnee']);
        });
    }
};