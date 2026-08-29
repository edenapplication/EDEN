<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rh_employes', function (Blueprint $table) {
            // ===== INFORMATIONS PERSONNELLES =====
            $table->string('nationalite', 100)->nullable()->after('lieu_naissance');
            $table->string('email', 150)->nullable()->after('telephone');
            
            // ===== INFORMATIONS PROFESSIONNELLES =====
            $table->foreignId('niveau_chelon_id')->nullable()->after('categorie')
                  ->constrained('rh_niveaux_chelons')->nullOnDelete();
            $table->foreignId('agence_site_id')->nullable()->after('service_id')
                  ->constrained('rh_agences_sites')->nullOnDelete();
            $table->foreignId('responsable_hierarchique_id')->nullable()->after('poste_id')
                  ->constrained('rh_employes')->nullOnDelete();
            $table->date('date_prise_fonction')->nullable()->after('date_integration');
            $table->date('date_fin_periode_essai')->nullable()->after('date_prise_fonction');
            $table->string('mode_paiement', 50)->nullable()->after('vague_paiement');
            
            // ===== INFORMATIONS CNPS =====
            $table->string('numero_cnps', 50)->nullable()->after('niu');
            $table->date('date_affiliation_cnps')->nullable()->after('numero_cnps');
            $table->string('centre_cnps', 100)->nullable()->after('date_affiliation_cnps');
            $table->string('situation_affiliation_cnps', 50)->nullable()->after('centre_cnps');
            
            // ===== INDEX =====
            $table->index('email');
            $table->index('numero_cnps');
            $table->index('responsable_hierarchique_id');
            $table->index('niveau_chelon_id');
            $table->index('agence_site_id');
        });
    }

    public function down(): void
    {
        Schema::table('rh_employes', function (Blueprint $table) {
            // Supprimer les clés étrangères
            $table->dropForeign(['niveau_chelon_id']);
            $table->dropForeign(['agence_site_id']);
            $table->dropForeign(['responsable_hierarchique_id']);
            
            // Supprimer les colonnes
            $table->dropColumn([
                'nationalite',
                'email',
                'niveau_chelon_id',
                'agence_site_id',
                'responsable_hierarchique_id',
                'date_prise_fonction',
                'date_fin_periode_essai',
                'mode_paiement',
                'numero_cnps',
                'date_affiliation_cnps',
                'centre_cnps',
                'situation_affiliation_cnps',
            ]);
        });
    }
};