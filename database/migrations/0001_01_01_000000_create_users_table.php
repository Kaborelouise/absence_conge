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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('nom')->nullable();
            $table->string('prenom')->nullable();
            $table->string('poste')->nullable();
            $table->string('signature')->nullable();//champs pas obligatoire
            $table->boolean('est_responsable_departement')->default(false);
            $table->boolean('est_responsable_direction')->default(false);
            // default(false) pour dire que par defaut un utilisateur n'est pas un responsable

            // quel role a cet utilisateur
            $table->foreignId('role_id')->constrained('roles');

            //Dans quel departement est ce utilisateur
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
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};






