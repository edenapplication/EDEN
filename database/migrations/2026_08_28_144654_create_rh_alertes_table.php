<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rh_alertes', function (Blueprint $table) {
            $table->id();
            
            // ===== LIENS =====
            $table->foreignId('employe_id')->nullable()->constrained('rh_employes')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            
            // ===== CONTENU =====
            $table->string('type', 50); // contrat, paie, cnps, absence, conge, retard, sanction, depart
            $table->string('titre', 255);
            $table->text('message');
            $table->string('lien', 255)->nullable(); // URL vers la ressource concernée
            $table->json('data')->nullable(); // Données supplémentaires
            
            // ===== STATUT =====
            $table->enum('statut', ['non_lu', 'lu', 'traite', 'ignore'])->default('non_lu');
            $table->enum('priorite', ['basse', 'normale', 'haute', 'critique'])->default('normale');
            
            // ===== DATES =====
            $table->timestamp('date_lecture')->nullable();
            $table->timestamp('date_traitement')->nullable();
            $table->timestamp('date_expiration')->nullable();
            
            $table->timestamps();
            
            // ===== INDEX =====
            $table->index('employe_id');
            $table->index('user_id');
            $table->index('type');
            $table->index('statut');
            $table->index('priorite');
            $table->index(['statut', 'priorite']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_alertes');
    }
};