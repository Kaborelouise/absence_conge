<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demande_absences', function (Blueprint $table) {
            $table->foreignId('session_administrative_id')
                ->nullable()
                ->after('user_id')
                ->constrained('sessions_demandes')
                ->nullOnDelete();
        });

        Schema::table('demande_conges', function (Blueprint $table) {
            $table->foreignId('session_administrative_id')
                ->nullable()
                ->after('user_id')
                ->constrained('sessions_demandes')
                ->nullOnDelete();
        });

        Schema::table('demande_jouissances', function (Blueprint $table) {
            $table->foreignId('session_administrative_id')
                ->nullable()
                ->after('user_id')
                ->constrained('sessions_demandes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('demande_absences', function (Blueprint $table) {
            $table->dropConstrainedForeignId('session_administrative_id');
        });

        Schema::table('demande_conges', function (Blueprint $table) {
            $table->dropConstrainedForeignId('session_administrative_id');
        });

        Schema::table('demande_jouissances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('session_administrative_id');
        });
    }
};