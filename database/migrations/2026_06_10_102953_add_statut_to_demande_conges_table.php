<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demande_conges', function (Blueprint $table) {
            $table->enum('statut', [
                'en_attente',
                'compilee',
                'validee',
                'refusee'
            ])->default('en_attente');
        });
    }

    public function down(): void
    {
        Schema::table('demande_conges', function (Blueprint $table) {
            $table->dropColumn('statut');
        });
    }
};