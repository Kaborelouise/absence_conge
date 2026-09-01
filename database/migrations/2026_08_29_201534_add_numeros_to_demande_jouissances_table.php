<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demande_jouissances', function (Blueprint $table) {
            $table->string('numero_cessation_service')->nullable();
            $table->string('numero_prise_service')->nullable();
            $table->string('numero_interim')->nullable();
            $table->foreignId('interimaire_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('demande_jouissances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('interimaire_id');
            $table->dropColumn(['numero_cessation_service', 'numero_prise_service', 'numero_interim']);
        });
    }
};