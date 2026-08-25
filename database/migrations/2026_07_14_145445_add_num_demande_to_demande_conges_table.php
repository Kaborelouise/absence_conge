<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('demande_conges', function (Blueprint $table) {
            $table->integer('num_demande')->nullable()->after('id');
        });

       
        DB::table('demande_conges')->whereNull('num_demande')->orderBy('id')->get()->each(function ($row) {
            DB::table('demande_conges')
                ->where('id', $row->id)
                ->update(['num_demande' => \Carbon\Carbon::parse($row->created_at)->timestamp + $row->id]);
           
        });
    }

    public function down(): void
    {
        Schema::table('demande_conges', function (Blueprint $table) {
            $table->dropColumn('num_demande');
        });
    }
};