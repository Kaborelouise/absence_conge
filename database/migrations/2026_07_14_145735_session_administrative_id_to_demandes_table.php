<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    //  Rattache chaque demande (absence, congé, jouissance) à la session
    //  Administrateuristrative (campagne annuelle) sous laquelle elle a été créée.
    //  Nullable + nullOnDelete : si une session est supprimée, on ne perd pas
    //  les demandes qui y étaient rattachées, elles perdent juste leur lien
    //  (plutôt qu'un ON DELETE CASCADE qui supprimerait les demandes elles-mêmes
    //   bien trop destructeur pour de l'historique Administrateuristratif).

    public function up(): void
    {
        Schema::table('demande_absences', function (Blueprint $table) {
            $table->foreignId('session_Administrateuristrative_id')
                ->nullable()
                ->after('user_id')
                ->constrained('sessions_demandes')
                ->nullOnDelete();
        });

        Schema::table('demande_conges', function (Blueprint $table) {
            $table->foreignId('session_Administrateuristrative_id')
                ->nullable()
                ->after('user_id')
                ->constrained('sessions_demandes')
                ->nullOnDelete();
        });

        Schema::table('demande_jouissances', function (Blueprint $table) {
            $table->foreignId('session_Administrateuristrative_id')
                ->nullable()
                ->after('user_id')
                ->constrained('sessions_demandes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('demande_absences', function (Blueprint $table) {
            $table->dropConstrainedForeignId('session_Administrateuristrative_id');
        });

        Schema::table('demande_conges', function (Blueprint $table) {
            $table->dropConstrainedForeignId('session_Administrateuristrative_id');
        });

        Schema::table('demande_jouissances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('session_Administrateuristrative_id');
        });
    }
};