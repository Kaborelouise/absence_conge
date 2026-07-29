<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demande_conges', function (Blueprint $table) {
            $table->boolean('abandonnee')
                  ->default(false)
                  ->after('statut');
        });
    }

    public function down(): void
    {
        Schema::table('demande_conges', function (Blueprint $table) {
            $table->dropColumn('abandonnee');
        });
    }
};