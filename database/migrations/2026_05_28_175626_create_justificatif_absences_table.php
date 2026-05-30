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
            //On ne stocke pas le fichier en base, juste son chemin
            $table->fichier_path('string');

            $table->string('type');
            //type de justificatif
            $table->foreignId('demande_absence_id')
                  ->onstrained('demande_absences')
                  ->onDelete('cascade');
                  //le justificatif a la clé étrangère car la demande peut exister sans justificatifs



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
