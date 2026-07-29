<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demande_conges', function (Blueprint $table) {

            if (!Schema::hasColumn('demande_conges', 'num_demande')) {
                $table->bigInteger('num_demande')
                      ->nullable()
                      ->after('id');
            }

        });
    }

    public function down(): void
    {
        Schema::table('demande_conges', function (Blueprint $table) {

            if (Schema::hasColumn('demande_conges', 'num_demande')) {
                $table->dropColumn('num_demande');
            }

        });
    }
};