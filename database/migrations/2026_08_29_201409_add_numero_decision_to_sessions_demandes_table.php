<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sessions_demandes', function (Blueprint $table) {
            $table->string('numero_decision')->nullable()->after('annee');
            $table->date('date_decision')->nullable()->after('numero_decision');
        });
    }

    public function down(): void
    {
        Schema::table('sessions_demandes', function (Blueprint $table) {
            $table->dropColumn(['numero_decision', 'date_decision']);
        });
    }
};