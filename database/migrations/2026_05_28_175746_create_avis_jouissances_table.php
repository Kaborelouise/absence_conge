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
        Schema::create('avis_jouissances', function (Blueprint $table) {
            $table->id();
            $table->enum('avis',['favorable', 'defavorable','en_attente'])->default('en_attente');
            $table->enum('type', ['Chef de Département', 'Responsable Direction', 'Agent RH', 'SG', 'DG', 'PCA']);
            $table->text('commentaire')->nullable();

            $table->foreignId('demande_jouissance_id')->constrained('demande_jouissances')->onDelete('cascade');
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avis_jouissances');
    }
};
