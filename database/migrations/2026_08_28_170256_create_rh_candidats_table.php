<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rh_candidats', function (Blueprint $table) {
            $table->id();
            
            // ===== INFORMATIONS PERSONNELLES =====
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->string('email', 150)->nullable();
            $table->string('telephone', 20)->nullable();
            $table->string('sexe', 1)->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance', 100)->nullable();
            $table->string('nationalite', 100)->nullable();
            $table->string('adresse', 255)->nullable();
            
            // ===== PROFESSIONNEL =====
            $table->string('poste_demande', 150)->nullable();
            $table->string('niveau_academique', 50)->nullable();
            $table->string('specialite', 100)->nullable();
            $table->integer('annees_experience')->default(0);
            $table->string('dernier_poste', 150)->nullable();
            $table->string('dernier_employeur', 150)->nullable();
            $table->text('competences')->nullable();
            
            // ===== SUIVI =====
            $table->foreignId('source_id')->nullable()->constrained('rh_sources_candidature')->onDelete('set null');
            $table->date('date_candidature');
            $table->decimal('salaire_souhaite', 15, 2)->nullable();
            $table->date('disponibilite')->nullable();
            
            // ===== STATUT =====
            $table->enum('statut', [
                'recu',              // Candidature reçue
                'preselectionne',    // Présélectionné
                'entretien_rh',      // Entretien RH
                'entretien_hierarchique', // Entretien hiérarchique
                'test',              // Phase de test
                'valide',            // Validé
                'embauche',          // Embauche
                'rejete'             // Rejeté
            ])->default('recu');
            
            // ===== DOCUMENTS =====
            $table->string('cv_path')->nullable();
            $table->string('lettre_motivation_path')->nullable();
            $table->string('diplomes_path')->nullable();
            
            // ===== OBSERVATIONS =====
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // ===== INDEX =====
            $table->index('statut');
            $table->index('email');
            $table->index('date_candidature');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_candidats');
    }
};