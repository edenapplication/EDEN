<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rh_contrats', function (Blueprint $table) {
            $table->id();
            
            // ===== LIENS =====
            $table->foreignId('employe_id')->constrained('rh_employes')->onDelete('cascade');
            $table->foreignId('type_contrat_id')->constrained('rh_types_contrat')->onDelete('restrict');
            $table->foreignId('direction_id')->nullable()->constrained('rh_directions')->onDelete('set null');
            $table->foreignId('service_id')->nullable()->constrained('rh_services')->onDelete('set null');
            $table->foreignId('poste_id')->nullable()->constrained('rh_postes')->onDelete('set null');
            $table->foreignId('agence_site_id')->nullable()->constrained('rh_agences_sites')->onDelete('set null');
            
            // ===== INFORMATIONS CONTRACTUELLES =====
            $table->string('numero_contrat', 50)->unique();
            $table->date('date_debut');
            $table->date('date_fin')->nullable(); // Null pour CDI
            $table->integer('periode_essai_jours')->nullable(); // Durée en jours
            $table->date('date_fin_periode_essai')->nullable(); // Calculée automatiquement
            $table->boolean('est_renouvelable')->default(false);
            $table->integer('nb_renouvellements')->default(0);
            $table->integer('renouvellement_max')->nullable();
            
            // ===== INFORMATIONS SALARIALES =====
            $table->decimal('salaire_base', 15, 2);
            $table->decimal('salaire_brut', 15, 2)->nullable(); // Peut différer du salaire de base
            $table->string('devise', 10)->default('FCFA');
            $table->string('mode_paiement', 50)->nullable();
            
            // ===== INFORMATIONS COMPLÉMENTAIRES =====
            $table->string('responsable_hierarchique', 100)->nullable();
            $table->text('conditions_particulieres')->nullable();
            $table->text('horaires')->nullable();
            $table->string('lieu_travail', 255)->nullable();
            
            // ===== STATUT & SUIVI =====
            $table->enum('statut', [
                'en_attente',      // En attente de validation
                'valide',          // Validé mais pas encore actif
                'actif',           // En cours
                'suspendu',        // Suspendu
                'renouvele',       // Renouvelé
                'termine',         // Terminé
                'resilie',         // Résilié
                'annule'           // Annulé
            ])->default('en_attente');
            
            $table->date('date_signature')->nullable();
            $table->date('date_validation')->nullable();
            $table->foreignId('valide_par')->nullable()->constrained('users')->onDelete('set null');
            
            // ===== DOCUMENTS =====
            $table->string('fichier_contrat_path')->nullable();
            $table->string('fichier_avenant_path')->nullable();
            
            // ===== OBSERVATIONS =====
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // ===== INDEX =====
            $table->index('employe_id');
            $table->index('type_contrat_id');
            $table->index('statut');
            $table->index('date_debut');
            $table->index('date_fin');
            $table->index('date_fin_periode_essai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_contrats');
    }
};