<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rh_departs', function (Blueprint $table) {
            $table->id();
            
            // ===== LIENS =====
            $table->foreignId('employe_id')->constrained('rh_employes')->onDelete('cascade');
            $table->foreignId('motif_depart_id')->nullable()->constrained('rh_motifs_depart')->onDelete('set null');
            $table->foreignId('dernier_contrat_id')->nullable()->constrained('rh_contrats')->onDelete('set null');
            
            // ===== INFORMATIONS =====
            $table->date('date_depart');
            $table->date('date_notification')->nullable();
            $table->date('date_preavis')->nullable();
            $table->string('motif_libre', 255)->nullable(); // Si motif personnalisé
            
            // ===== DOCUMENTS =====
            $table->string('lettre_demission_path')->nullable();
            $table->string('attestation_travail_path')->nullable();
            $table->string('certificat_travail_path')->nullable();
            
            // ===== STATUT =====
            $table->enum('statut', [
                'en_attente',    // En attente de validation
                'valide',        // Validé par la RH
                'en_cours',      // En cours de traitement
                'termine',       // Traitement terminé
                'annule'         // Annulé
            ])->default('en_attente');
            
            // ===== VALIDATION =====
            $table->foreignId('valide_par')->nullable()->constrained('users')->onDelete('set null');
            $table->date('date_validation')->nullable();
            
            // ===== OBSERVATIONS =====
            $table->text('observations')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // ===== INDEX =====
            $table->index('employe_id');
            $table->index('date_depart');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_departs');
    }
};