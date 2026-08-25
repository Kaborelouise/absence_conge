<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demande_jouissances', function (Blueprint $table) {
            $table->string('num_certificat_cessation')->nullable()->after('certificat_cessation');
            $table->timestamp('certificat_cessation_genere_at')->nullable()->after('num_certificat_cessation');
        });
    }

    public function down(): void
    {
        Schema::table('demande_jouissances', function (Blueprint $table) {
            $table->dropColumn(['num_certificat_cessation', 'certificat_cessation_genere_at']);
        });
    }
};