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
        Schema::create('departements', function (Blueprint $table) {
            $table->id();
<<<<<<< HEAD
            //Ex: DRH
            $table->string('libelle_court');
            //Ex: Direction des Ressources Humaines
            $table->string('libelle_long');
            $table->foreignId('direction_id')
                  ->constrained('directions')
                  ->onDelete('cascade');
                  //On stocke l'Id de la direction a laquelle appartient ce departement
                  //onDelete('cascade'): si la direction est supprimée, ses departements sont aussi supprimés 

=======
>>>>>>> 1ce37f274bc27af71ef5858c73775e967614fd85
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departements');
    }
};
