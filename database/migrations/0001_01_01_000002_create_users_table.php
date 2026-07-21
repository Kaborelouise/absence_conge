<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->integer('matricule')->unique();
            $table->string('nom');
             $table->string('prenom');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('poste')->nullable();
            $table->string('signature')->nullable();
            $table->boolean('est_responsable_departement')->default(false);
            $table->boolean('est_Responsable Direction')->default(false);

            $table->integer('solde_conge')->default(30);
             $table->integer('solde_absence')->default(10);
            
            // quel role a cet utilisateur
            $table->foreignId('role_id')->constrained('roles');

            

            //Dans quel departement est cet utilisateur
            $table->foreignId('departement_id')
                  ->constrained('departements');


            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_Agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};




