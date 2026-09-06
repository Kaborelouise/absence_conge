<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parametres_documents', function (Blueprint $table) {
            $table->string('suffixe_decision', 50)->default('DG/SG/DRH');
            $table->string('suffixe_certificat', 50)->default('DG/SG');
            $table->string('suffixe_interim', 50)->default('SG/DRH');
        });
    }

    public function down(): void
    {
        Schema::table('parametres_documents', function (Blueprint $table) {
            $table->dropColumn(['suffixe_decision', 'suffixe_certificat', 'suffixe_interim']);
        });
    }
};