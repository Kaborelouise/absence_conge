<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demande_absences', function (Blueprint $table) {
            $table->timestamp('cloturee_at')->nullable()->after('statut');
        });
    }

    public function down(): void
    {
        Schema::table('demande_absences', function (Blueprint $table) {
            $table->dropColumn('cloturee_at');
        });
    }
};