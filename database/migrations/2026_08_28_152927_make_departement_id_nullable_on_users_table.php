<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['departement_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('departement_id')
                  ->nullable()
                  ->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('departement_id')
                  ->references('id')->on('departements')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['departement_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('departement_id')
                  ->nullable(false)
                  ->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('departement_id')
                  ->references('id')->on('departements')
                  ->cascadeOnDelete();
        });
    }
};