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
        Schema::create('demande_conges', function (Blueprint $table) {
            $table->id();

            $table->enum('lieu_jouissance',[
                'Burkina faso',
                'Afrique',
                'Canada',
                'Asie',
                'Amerique',
                'Europe',
            ]);

            $table->enum('statut', [
                'en_attente',
                'compilee',
                'validee',
                 'refusee'
            ])->default('en_attente');
            $table->foreignId('user_id')
                ->constrained('users');

            $table->timestamps();
        });

    }
    public function down(): void
    {
        Schema::dropIfExists('demande_conges');
    }
};
