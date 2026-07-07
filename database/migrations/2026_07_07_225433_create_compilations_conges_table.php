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
        Schema::create('compilations_conges', function (Blueprint $table) {
            $table->id();
            // Année concernée par la compilation
            $table->integer('annee');
            // RH qui a compilé
            $table->foreignId('compiled_by')->constrained('users');
            // Date de compilation
            $table->timestamp('compiled_at')->nullable();
            // Date de décompilation si annulée
            $table->timestamp('decompilee_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compilations_conges');
    }
};
