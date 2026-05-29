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
<<<<<<< HEAD
            $table->integer('num_demande')->unique();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->string('motif');
            $table->foreignId('utilisateur_id')
                  ->constrained('utilisateurs');
                  //qui a fait la demande
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
        Schema::dropIfExists('demande_absences');
    }
};
