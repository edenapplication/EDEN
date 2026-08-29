<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rh_cnps_affiliations', function (Blueprint $table) {
            $table->id();
            
            // ===== LIEN EMPLOYÉ =====
            $table->foreignId('employe_id')->constrained('rh_employes')->onDelete('cascade');
            
            // ===== INFORMATIONS CNPS =====
            $table->string('numero_cnps', 50)->nullable();
            $table->date('date_affiliation')->nullable();
            $table->string('centre_cnps', 100)->nullable();
            $table->string('situation_affiliation', 50)->nullable(); // affilie, non_affilie, en_cours, radie
            
            // ===== SUIVI =====
            $table->string('categorie_cnps', 50)->nullable();
            $table->decimal('salaire_soumis', 15, 2)->nullable(); // Salaire soumis à cotisation
            
            // ===== DOCUMENTS =====
            $table->string('attestation_affiliation_path')->nullable();
            $table->string('carte_cnps_path')->nullable();
            
            // ===== OBSERVATIONS =====
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // ===== INDEX =====
            $table->unique('employe_id');
            $table->index('numero_cnps');
            $table->index('date_affiliation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_cnps_affiliations');
    }
};