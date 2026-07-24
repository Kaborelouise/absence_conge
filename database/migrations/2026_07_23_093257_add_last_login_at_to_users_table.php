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
    Schema::create('activity_logs', function (Blueprint $table) {
        $table->id();
        // Utilisateur qui a fait l'action
        $table->foreignId('user_id')->constrained('users');
        // Action : create, update, delete, read
        $table->string('action');
        // Modèle concerné : User, DemandeAbsence, etc.
        $table->string('model');
        // Id de l'enregistrement concerné
        $table->unsignedBigInteger('model_id')->nullable();
        // Description de l'action
        $table->text('description')->nullable();
        $table->timestamps();
    });
}
};
