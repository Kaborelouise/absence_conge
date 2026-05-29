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
<<<<<<< HEAD
            $table->string('lieu_jouissance');
            $table->foreignId('utilisateur_id')
                ->constrained('utilisateurs');

            $table->timestamps();
        });

=======
            $table->timestamps();
        });
>>>>>>> 1ce37f274bc27af71ef5858c73775e967614fd85
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demande_conges');
    }
};
