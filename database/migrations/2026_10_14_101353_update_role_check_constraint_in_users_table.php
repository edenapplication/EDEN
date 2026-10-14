<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ⚠️ SQLite ne supporte pas ALTER pour modifier une contrainte CHECK
        // Solution : recréer la table avec la nouvelle contrainte

        // 1. Sauvegarder les données existantes
        $users = DB::table('users')->get();

        // 2. Supprimer l'ancienne table
        Schema::dropIfExists('users');

        // 3. Recréer avec la nouvelle contrainte incluant 'comptable'
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('reference')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'rh', 'commercial', 'geometre', 'comptable'])
                  ->default('commercial');
            $table->boolean('actif')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        // 4. Réinsérer les données
        foreach ($users as $user) {
            DB::table('users')->insert((array) $user);
        }
    }

    public function down(): void
    {
        // Revenir à l'ancienne contrainte (sans 'comptable')
        $users = DB::table('users')->get();
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('reference')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'rh', 'commercial', 'geometre'])
                  ->default('commercial');
            $table->boolean('actif')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        foreach ($users as $user) {
            DB::table('users')->insert((array) $user);
        }
    }
};