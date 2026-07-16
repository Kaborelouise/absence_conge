<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compilations_conges', function (Blueprint $table) {
            if (!Schema::hasColumn('compilations_conges', 'session_administrative_id')) {
                $table->foreignId('session_administrative_id')
                    ->nullable()
                    ->after('annee')
                    ->constrained('sessions_demandes')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('compilations_conges', function (Blueprint $table) {
            if (Schema::hasColumn('compilations_conges', 'session_administrative_id')) {
                $table->dropConstrainedForeignId('session_administrative_id');
            }
        });
    }
};