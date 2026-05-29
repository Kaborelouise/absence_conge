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
        Schema::create('justificatif_absences', function (Blueprint $table) {
            $table->id();
<<<<<<< HEAD
            //On ne stocke pas le fichier en base, juste son chemin
            $table->fichier_path('string');

            $table->string('type');
            $table->foreignId('demande_absence_ide')
                  ->onstrained('demande_absences')
                  ->onDelete('cascade');
                  //le justificatif porte le lien car une demande peut ne pas avoir de justificatif



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
        Schema::dropIfExists('justificatif_absences');
    }
};
