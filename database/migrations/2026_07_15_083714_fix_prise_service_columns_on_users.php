<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'date_prise_service')) {
                $table->date('date_prise_service')->nullable()->after('poste');
            }

            if (!Schema::hasColumn('users', 'certificat_prise_service')) {
                $table->string('certificat_prise_service')->nullable()->after('date_prise_service');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'certificat_prise_service')) {
                $table->dropColumn('certificat_prise_service');
            }

            if (Schema::hasColumn('users', 'date_prise_service')) {
                $table->dropColumn('date_prise_service');
            }
        });
    }
};