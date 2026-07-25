<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('date_prise_service')->nullable()->after('poste');
            $table->string('certificat_prise_service')->nullable()->after('date_prise_service');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['date_prise_service', 'certificat_prise_service']);
        });
    }
};