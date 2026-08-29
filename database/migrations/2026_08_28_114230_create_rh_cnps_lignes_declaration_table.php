<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rh_cnps_lignes_declaration', function (Blueprint $table) {
            $table->id();
            
            // ===== LIENS =====
            $table->foreignId('declaration_id')->constrained('rh_cnps_declarations')->onDelete('cascade');
            $table->foreignId('employe_id')->constrained('rh_employes')->onDelete('cascade');
            $table->foreignId('bulletin_paie_id')->nullable()->constrained('rh_bulletins_paie')->onDelete('set null');
            
            // ===== INFORMATIONS EMPLOYÉ =====
            $table->string('numero_cnps', 50)->nullable();
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->string('matricule', 50);
            
            // ===== MONTANTS =====
            $table->decimal('salaire_soumis', 15, 2)->default(0);
            $table->decimal('cotisation_salariale', 15, 2)->default(0);
            $table->decimal('cotisation_patronale', 15, 2)->default(0);
            $table->decimal('total_cnps', 15, 2)->default(0);
            
            // ===== OBSERVATIONS =====
            $table->text('observations')->nullable();
            
            $table->timestamps();
            
            // ===== INDEX =====
            $table->index('declaration_id');
            $table->index('employe_id');
            $table->index('numero_cnps');
            $table->unique(['declaration_id', 'employe_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_cnps_lignes_declaration');
    }
};