<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions_demandes', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->integer('annee');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->boolean('active_absence')->default(true);
            $table->boolean('active_conge')->default(true);
            $table->boolean('active_jouissance')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions_demandes');
    }
};