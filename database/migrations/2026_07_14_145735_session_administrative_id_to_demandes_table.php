<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['demande_absences', 'demande_conges', 'demande_jouissances'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'session_Administrateuristrative_id')) {
                    $table->foreignId('session_Administrateuristrative_id')
                        ->nullable()
                        ->after('user_id')
                        ->constrained('sessions_demandes')
                        ->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['demande_absences', 'demande_conges', 'demande_jouissances'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'session_Administrateuristrative_id')) {
                    $table->dropConstrainedForeignId('session_Administrateuristrative_id');
                }
            });
        }
    }
};