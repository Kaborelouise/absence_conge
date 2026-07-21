<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Corrige le bug du "N° Demande" vide sur les demandes de congé.
     *
     * Cause du bug : la colonne num_demande n'existait tout simplement pas dans
     * la table demande_conges (voir la migration d'origine), alors que le
     * contrôleur et la vue s'attendaient à ce qu'elle existe. Eloquent retournait
     * donc silencieusement null sur cet attribut, sans erreur visible.
     *
     * On ajoute la colonne en deux temps dans la même migration :
     * 1. Ajout nullable (pour ne pas casser l'insertion des lignes déjà en base).
     * 2. Backfill des lignes existantes avec un numéro basé sur leur created_at
     *    (converti en timestamp Unix), pour garantir un numéro unique et non nul
     *    même pour les demandes créées avant ce correctif.
     * 3. On NE rend PAS la colonne unique/not-null en dur ici, par prudence :
     *    si deux demandes existantes ont exactement le même created_at (peu
     *    probable mais possible), une contrainte unique ferait échouer la
     *    migration. Le contrôleur (DemandeCongeController::store) continue de
     *    générer un num_demande unique via time() à chaque nouvelle création,
     *    donc l'unicité est déjà garantie en pratique pour toute nouvelle ligne.
     */
    public function up(): void
    {
        Schema::table('demande_conges', function (Blueprint $table) {
            $table->integer('num_demande')->nullable()->after('id');
        });

        // Backfill des lignes existantes : on utilise le timestamp Unix de
        // created_at comme num_demande, cohérent avec ce que fait déjà
        // DemandeCongeController::store() pour les nouvelles demandes (time()).
        DB::table('demande_conges')->whereNull('num_demande')->orderBy('id')->get()->each(function ($row) {
            DB::table('demande_conges')
                ->where('id', $row->id)
                ->update(['num_demande' => \Carbon\Carbon::parse($row->created_at)->timestamp + $row->id]);
            // Le "+ $row->id" évite les doublons si plusieurs lignes partAgent
            // exactement la même seconde de création.
        });
    }

    public function down(): void
    {
        Schema::table('demande_conges', function (Blueprint $table) {
            $table->dropColumn('num_demande');
        });
    }
};