<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avis_absences', function (Blueprint $table) {
            $table->foreignId('user_id')
                  ->nullable()
                  ->after('demande_absence_id')
                  ->constrained()
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('avis_absences', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};