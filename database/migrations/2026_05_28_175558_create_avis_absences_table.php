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
        Schema::create('avis_absences', function (Blueprint $table) {
            $table->id();
            $table->enum('avis', ['favorable', 'defavorable', 'en_attente'])->default('en_attente');
            // enum : on doit choisir une valeur 
            $table->enum('type', ['chef_departement', 'responsable_direction', 'agent_rh', 'sg', 'dg', 'pca']);
          // type, qui donne l'avis ? Car une demande reçoit plusieurs avis
            $table->text('commentaire')->nullable();
            
            $table->foreignId('demande_absence_id')
                 ->constrained('demande_absences')
                 ->onDelete('cascade');
            // Sur quelle demande porte l'avis 
            $table->timestamps();
           
           });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avis_absences');
    }
};
