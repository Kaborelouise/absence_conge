<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
        public function up(): void
        {
            Schema::table('users', function (Blueprint $table) {
                // Date de prise de service pour calculer l'éligibilité congé/jouissance
                $table->date('date_prise_service')->nullable()->after('departement_id');
                // Chemin du certificat d'intégration ou arrêté uploadé
                $table->string('certificat_integration')->nullable()->after('date_prise_service');
            });
        }

        public function down(): void
        {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['date_prise_service', 'certificat_integration']);
            });
        }
};
