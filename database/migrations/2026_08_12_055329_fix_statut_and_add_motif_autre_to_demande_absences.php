<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // On récupère le nom exact de la contrainte check existante sur le statut
        $constraint = DB::selectOne("
            SELECT conname FROM pg_constraint
            WHERE conrelid = 'demande_absences'::regclass
            AND contype = 'c'
            AND pg_get_constraintdef(oid) LIKE '%statut%'
        ");

        if ($constraint) {
            DB::statement("ALTER TABLE demande_absences DROP CONSTRAINT {$constraint->conname}");
        }

        DB::statement("ALTER TABLE demande_absences ADD CONSTRAINT demande_absences_statut_check
            CHECK (statut IN ('en_attente', 'en_cours', 'validee', 'rejetee', 'abandonnee'))");

        Schema::table('demande_absences', function (Blueprint $table) {
            $table->string('motif_autre')->nullable()->after('motif');
        });
    }

    public function down(): void
    {
        Schema::table('demande_absences', function (Blueprint $table) {
            $table->dropColumn('motif_autre');
        });

        DB::statement("ALTER TABLE demande_absences DROP CONSTRAINT IF EXISTS demande_absences_statut_check");
        DB::statement("ALTER TABLE demande_absences ADD CONSTRAINT demande_absences_statut_check
            CHECK (statut IN ('en_attente', 'en_cours', 'validee', 'rejetee'))");
    }
};