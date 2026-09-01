<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sessions_demandes', function (Blueprint $table) {
            if (!Schema::hasColumn('sessions_demandes', 'soldes_reinitialises')) {
                $table->boolean('soldes_reinitialises')->default(false);
            }
            if (!Schema::hasColumn('sessions_demandes', 'libelle')) {
                $table->string('libelle')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('sessions_demandes', function (Blueprint $table) {
            $table->dropColumn(['soldes_reinitialises', 'libelle']);
        });
    }
};