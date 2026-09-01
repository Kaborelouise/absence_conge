<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametres_documents', function (Blueprint $table) {
            $table->id();
            $table->string('ministere_libelle')->default('MINISTERE DE LA TRANSITION DIGITALE, DES POSTES ET DES COMMUNICATIONS ELECTRONIQUES');
            $table->string('sigle_ministere')->default('MTDPCE');
            $table->string('sigle_secretariat')->default('SG');
            $table->string('sigle_agence')->default('ANPTIC');
            $table->unsignedTinyInteger('nb_chiffres_decision')->default(5);
            $table->unsignedTinyInteger('nb_chiffres_cessation')->default(7);
            $table->unsignedTinyInteger('nb_chiffres_prise_service')->default(7);
            $table->unsignedTinyInteger('nb_chiffres_interim')->default(3);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parametres_documents');
    }
};