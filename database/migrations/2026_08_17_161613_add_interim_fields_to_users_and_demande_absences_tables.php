<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter le genre dans la table users
        Schema::table('users', function (Blueprint $table) {
            $table->enum('genre', ['M', 'F'])
                ->nullable()
                ->after('poste');
        });

        // Ajouter les informations liées à l'intérim
        Schema::table('demande_absences', function (Blueprint $table) {
            $table->foreignId('interimaire_id')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->string('num_note_interim')
                ->nullable()
                ->after('interimaire_id');

            $table->timestamp('note_interim_generee_at')
                ->nullable()
                ->after('num_note_interim');
        });

        // Convertir les anciennes valeurs de "interimaire"
        // vers "interimaire_id"
        DB::table('demande_absences')
            ->whereNotNull('interimaire')
            ->where('interimaire', '!=', '')
            ->orderBy('id')
            ->each(function ($demande) {

                $interimaire = trim($demande->interimaire);

                $user = DB::table('users')
                    ->whereRaw(
                        "LOWER(TRIM(CONCAT(nom, ' ', prenom))) = ?",
                        [strtolower($interimaire)]
                    )
                    ->first();

                if ($user) {
                    DB::table('demande_absences')
                        ->where('id', $demande->id)
                        ->update([
                            'interimaire_id' => $user->id,
                        ]);
                }
            });

        // Supprimer l'ancien champ texte
        Schema::table('demande_absences', function (Blueprint $table) {
            $table->dropColumn('interimaire');
        });
    }

    public function down(): void
    {
        // Restaurer l'ancien champ interimaire
        Schema::table('demande_absences', function (Blueprint $table) {
            $table->string('interimaire')
                ->nullable()
                ->after('id');
        });

        // Restaurer les noms des intérimaires
        DB::table('demande_absences')
            ->whereNotNull('interimaire_id')
            ->orderBy('id')
            ->each(function ($demande) {

                $user = DB::table('users')
                    ->where('id', $demande->interimaire_id)
                    ->first();

                if ($user) {
                    DB::table('demande_absences')
                        ->where('id', $demande->id)
                        ->update([
                            'interimaire' => trim($user->nom . ' ' . $user->prenom),
                        ]);
                }
            });

        // Supprimer les nouveaux champs
        Schema::table('demande_absences', function (Blueprint $table) {
            $table->dropForeign(['interimaire_id']);

            $table->dropColumn([
                'interimaire_id',
                'num_note_interim',
                'note_interim_generee_at',
            ]);
        });

        // Supprimer genre
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('genre');
        });
    }
};