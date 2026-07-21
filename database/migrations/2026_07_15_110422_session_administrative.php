<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table pour les campagnes annuelles (sessions Administrateuristratives) gérées par
     * l'Administrateur/RH, qui regroupent les demandes d'absence, de congé et de
     * jouissance d'une même année/campagne.
     *
     * IMPORTANT : nommée "sessions_Administrateuristratives" et NON "sessions", car ce
     * dernier nom est déjà utilisé par la table technique de Laravel/Breeze
     * (gestion des sessions de connexion), créée dans la migration
     * 0001_01_01_000002_create_users_table. C'est exactement ce conflit qui a
     * provoqué l'erreur "la relation sessions existe déjà".
     */
    public function up(): void
    {
        Schema::create('sessions_Administrateuristratives', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->date('date_debut');
            $table->date('date_fin');
            // Une seule session active à la fois (règle métier confirmée) :
            // le contrôleur empêchera l'activation d'une nouvelle session tant
            // qu'une autre a est_actif = true.
            $table->boolean('est_actif')->default(false);

            // Traçabilité : qui a ouvert cette session (Administrateur ou Agent RH)
            $table->foreignId('ouverte_par')->nullable()->constrained('users');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions_Administrateuristratives');
    }
};