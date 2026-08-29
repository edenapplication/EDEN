<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rh_accidents_travail', function (Blueprint $table) {
            $table->id();
            
            // ===== LIENS =====
            $table->foreignId('employe_id')->constrained('rh_employes')->onDelete('cascade');
            $table->foreignId('declare_par')->nullable()->constrained('users')->onDelete('set null');
            
            // ===== INFORMATIONS =====
            $table->date('date_accident');
            $table->time('heure')->nullable();
            $table->string('lieu', 255);
            $table->string('circonstances', 255)->nullable();
            $table->text('description');
            $table->string('nature_blessures', 255)->nullable();
            
            // ===== TÉMOINS =====
            $table->string('temoin1_nom', 150)->nullable();
            $table->string('temoin1_tel', 20)->nullable();
            $table->string('temoin2_nom', 150)->nullable();
            $table->string('temoin2_tel', 20)->nullable();
            
            // ===== SUIVI =====
            $table->text('prise_en_charge')->nullable();
            $table->text('suivi')->nullable();
            $table->date('date_retour')->nullable();
            
            // ===== DOCUMENTS =====
            $table->string('rapport_path')->nullable();
            $table->string('constat_path')->nullable();
            $table->string('certificat_medical_path')->nullable();
            
            // ===== STATUT =====
            $table->enum('statut', ['declare', 'en_cours', 'cloture', 'annule'])->default('declare');
            
            $table->timestamps();
            
            // ===== INDEX =====
            $table->index('employe_id');
            $table->index('date_accident');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_accidents_travail');
    }
};