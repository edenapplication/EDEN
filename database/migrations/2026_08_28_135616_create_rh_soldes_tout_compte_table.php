<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rh_soldes_tout_compte', function (Blueprint $table) {
            $table->id();
            
            // ===== LIENS =====
            $table->foreignId('depart_id')->constrained('rh_departs')->onDelete('cascade');
            $table->foreignId('employe_id')->constrained('rh_employes')->onDelete('cascade');
            
            // ===== MONTANTS =====
            $table->decimal('salaire_base', 15, 2)->default(0);
            $table->decimal('indemnite_conges', 15, 2)->default(0);
            $table->decimal('indemnite_preavis', 15, 2)->default(0);
            $table->decimal('indemnite_licenciement', 15, 2)->default(0);
            $table->decimal('prime_anciennete', 15, 2)->default(0);
            $table->decimal('autres_indemnites', 15, 2)->default(0);
            $table->decimal('total_brut', 15, 2)->default(0);
            
            // ===== DÉDUCTIONS =====
            $table->decimal('cnps', 15, 2)->default(0);
            $table->decimal('impots', 15, 2)->default(0);
            $table->decimal('autres_deductions', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0);
            
            // ===== NET =====
            $table->decimal('net_a_payer', 15, 2)->default(0);
            
            // ===== PAIEMENT =====
            $table->date('date_paiement')->nullable();
            $table->string('mode_paiement', 50)->nullable();
            $table->string('reference_paiement', 50)->nullable();
            
            // ===== STATUT =====
            $table->enum('statut', [
                'a_payer',       // À payer
                'paye',          // Payé
                'annule'         // Annulé
            ])->default('a_payer');
            
            // ===== DOCUMENTS =====
            $table->string('document_path')->nullable();
            
            // ===== OBSERVATIONS =====
            $table->text('observations')->nullable();
            
            $table->timestamps();
            
            // ===== INDEX =====
            $table->index('employe_id');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_soldes_tout_compte');
    }
};