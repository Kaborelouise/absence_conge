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
        Schema::create('directions', function (Blueprint $table) {
            $table->id();
<<<<<<< HEAD
            //ex: DSA
            $table->string('libelle_court');

            //Ex: Diretion des Systèmes Applicatifs
            $table->string('libelle_long');
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
        Schema::dropIfExists('directions');
    }
};
