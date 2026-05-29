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
        Schema::create('demande_jouissances', function (Blueprint $table) {
            $table->id();
            $table->integer('num_demande')->unique();
            
            $table->date('date_debut');
            $table->date('date_fin');
            $table->integer('nombre_jour');
            $table->foreignId('utilisateur_id')
                  ->constrained('utilisateurs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demande_jouissances');
    }
};
