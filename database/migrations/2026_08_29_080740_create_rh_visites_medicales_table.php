<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rh_visites_medicales', function (Blueprint $table) {
            $table->id();
            
            // ===== LIENS =====
            $table->foreignId('employe_id')->constrained('rh_employes')->onDelete('cascade');
            $table->foreignId('medecin_id')->nullable()->constrained('users')->onDelete('set null');
            
            // ===== INFORMATIONS =====
            $table->enum('type', ['embauche', 'periodique', 'reprise', 'accident']);
            $table->date('date_visite');
            $table->string('medecin_nom', 150)->nullable();
            $table->string('medecin_tel', 20)->nullable();
            $table->string('etablissement', 150)->nullable();
            
            // ===== RÉSULTATS =====
            $table->enum('aptitude', ['apte', 'apte_avec_restriction', 'inapte'])->default('apte');
            $table->text('restrictions')->nullable();
            $table->text('observations')->nullable();
            
            // ===== SUIVI =====
            $table->date('prochaine_visite')->nullable();
            $table->boolean('certificat_fourni')->default(false);
            $table->string('certificat_path')->nullable();
            
            // ===== STATUT =====
            $table->enum('statut', ['planifie', 'effectue', 'annule'])->default('planifie');
            
            $table->timestamps();
            
            // ===== INDEX =====
            $table->index('employe_id');
            $table->index('date_visite');
            $table->index('prochaine_visite');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_visites_medicales');
    }
};