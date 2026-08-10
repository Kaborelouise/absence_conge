<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 
    public function up(): void
    {
        Schema::create('sessions_Administrateuristratives', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->date('date_debut');
            $table->date('date_fin');
           
            // le contrôleur empêchera l'activation d'une nouvelle session tant
            $table->boolean('est_actif')->default(false);

            // Traçabilité qui a ouvert cette session (Administrateur ou Agent RH)
            $table->foreignId('ouverte_par')->nullable()->constrained('users');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions_Administrateuristratives');
    }
};