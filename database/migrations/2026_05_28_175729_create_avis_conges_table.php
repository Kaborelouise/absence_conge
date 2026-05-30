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
        Schema::create('avis_conges', function (Blueprint $table) {
            $table->id();
            $table->enum('avis', ['favorable', 'defavorable', 'en_attente']);
            $table->text('commentaire')->nullable();
            $table->enum('type', ['agent_rh']);
             $table->enum('type', ['chef_departement', 'responsable_direction', 'agent_rh', 'sg', 'dg', 'pca']);
            $table->foreignId('demande_conge_id')
                  ->constrained('demande_conges')
                  ->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avis_conges');
    }
};
