<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rh_certificats_cessation', function (Blueprint $table) {
            $table->id();
            
            // ===== LIENS =====
            $table->foreignId('depart_id')->constrained('rh_departs')->onDelete('cascade');
            $table->foreignId('employe_id')->constrained('rh_employes')->onDelete('cascade');
            
            // ===== INFORMATIONS =====
            $table->string('reference', 50)->unique();
            $table->date('date_emission');
            $table->date('date_effet');
            
            // ===== CONTENU =====
            $table->text('motif')->nullable();
            $table->text('mention_speciale')->nullable();
            
            // ===== DOCUMENTS =====
            $table->string('document_path')->nullable();
            
            // ===== STATUT =====
            $table->enum('statut', [
                'brouillon',     // En cours de rédaction
                'valide',        // Validé
                'delivre'        // Délivré à l'employé
            ])->default('brouillon');
            
            // ===== VALIDATION =====
            $table->foreignId('valide_par')->nullable()->constrained('users')->onDelete('set null');
            $table->date('date_validation')->nullable();
            
            $table->timestamps();
            
            // ===== INDEX =====
            $table->index('employe_id');
            $table->index('reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_certificats_cessation');
    }
};