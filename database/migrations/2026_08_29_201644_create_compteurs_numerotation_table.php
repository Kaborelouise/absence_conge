<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compteurs_numerotation', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->integer('annee');
            $table->unsignedInteger('dernier_numero')->default(0);
            $table->timestamps();

            $table->unique(['type', 'annee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compteurs_numerotation');
    }
};