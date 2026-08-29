<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rh_cnps_declarations', function (Blueprint $table) {
            $table->id();
            
            // ===== PÉRIODE =====
            $table->string('periode', 7); // YYYY-MM
            $table->integer('mois')->nullable();
            $table->integer('annee')->nullable();
            
            // ===== INFORMATIONS =====
            $table->string('reference', 50)->unique();
            $table->date('date_declaration')->nullable();
            $table->date('date_echeance')->nullable();
            $table->date('date_paiement')->nullable();
            
            // ===== MONTANTS =====
            $table->decimal('total_salaire_soumis', 15, 2)->default(0);
            $table->decimal('total_cotisation_salariale', 15, 2)->default(0);
            $table->decimal('total_cotisation_patronale', 15, 2)->default(0);
            $table->decimal('total_cnps', 15, 2)->default(0);
            $table->decimal('total_penalites', 15, 2)->default(0);
            
            // ===== STATUT =====
            $table->enum('statut', [
                'a_declarer',      // À déclarer
                'declare',         // Déclaré
                'facture_recue',   // Facture reçue
                'paye',            // Payé
                'justifie'         // Justifié (archivé)
            ])->default('a_declarer');
            
            // ===== DOCUMENTS =====
            $table->string('fichier_dipe_path')->nullable(); // Déclaration Individuelle de Paie des Employés
            $table->string('fichier_facture_path')->nullable();
            $table->string('fichier_justificatif_path')->nullable();
            
            // ===== VALIDATION =====
            $table->foreignId('valide_par')->nullable()->constrained('users')->onDelete('set null');
            $table->date('date_validation')->nullable();
            
            // ===== OBSERVATIONS =====
            $table->text('observations')->nullable();
            
            $table->timestamps();
            
            // ===== INDEX =====
            $table->index('periode');
            $table->index('statut');
            $table->index('date_declaration');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_cnps_declarations');
    }
};