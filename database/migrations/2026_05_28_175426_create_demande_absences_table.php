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
        Schema::create('demande_absences', function (Blueprint $table) {
            $table->id();
            $table->string('interimaire')->nullable;
            $table->integer('num_demande')->unique();
            $table->boolean('retenue_salaire')->default(false);
            $table->date('date_debut');
            $table->date('date_fin');
            $table->string('motif');
            $table->enum('statut', [
                'en_attente',    // Demande soumise, mais pas encore traitée
                'en_cours',      // En cours de traitement (avis en attente)
                'validee',       // Validée par SG ou DG
                'rejetee'        // Rejetée à n'importe quelle niveau
                 ])->default('en_attente');

            $table->foreignId('user_id')
                  ->constrained('users');
                  //qui a fait la demande
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demande_absences');
    }
};
