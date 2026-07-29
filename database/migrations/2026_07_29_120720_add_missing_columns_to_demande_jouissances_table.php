<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demande_jouissances', function (Blueprint $table) {
            $table->boolean('abandonnee')->default(false)->after('statut');
            $table->string('certificat_cessation')->nullable()->after('abandonnee');
            $table->string('certificat_prise_service')->nullable()->after('certificat_cessation');
            $table->timestamp('cloturee_at')->nullable()->after('certificat_prise_service');
        });
    }

    public function down(): void
    {
        Schema::table('demande_jouissances', function (Blueprint $table) {
            $table->dropColumn([
                'abandonnee',
                'certificat_cessation',
                'certificat_prise_service',
                'cloturee_at',
            ]);
        });
    }
};