<?php
// database/migrations/2026_01_15_000000_create_feb_destinataires_table.php

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
        Schema::create('feb_destinataires', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->timestamps();
            
            // Index pour les recherches
            $table->index('nom');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feb_destinataires');
    }
};