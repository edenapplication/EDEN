<?php
// database/migrations/2026_01_15_000001_create_feb_destinataire_fiche_table.php

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
        Schema::create('feb_destinataire_fiche', function (Blueprint $table) {
            $table->id();
            
            // Clés étrangères
            $table->foreignId('fiche_id')
                  ->constrained('feb_fiches')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
                  
            $table->foreignId('destinataire_id')
                  ->constrained('feb_destinataires')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
            
            // Ordre d'affichage
            $table->integer('ordre')->default(0);
            
            $table->timestamps();
            
            // Contrainte d'unicité pour éviter les doublons
            $table->unique(['fiche_id', 'destinataire_id'], 'unique_fiche_destinataire');
            
            // Index pour les performances
            $table->index(['fiche_id', 'destinataire_id']);
            $table->index('ordre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feb_destinataire_fiche');
    }
};