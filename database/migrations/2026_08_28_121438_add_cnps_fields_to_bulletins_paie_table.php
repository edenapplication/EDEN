<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rh_bulletins_paie', function (Blueprint $table) {
            // ===== LIEN AVEC LE CONTRAT =====
            $table->foreignId('contrat_id')->nullable()->after('employe_id')
                  ->constrained('rh_contrats')->nullOnDelete();
            
            // ===== CHAMPS CNPS =====
            $table->decimal('base_cnps', 15, 2)->nullable()->after('salaire_brut');
            $table->decimal('cnps_salariale', 15, 2)->default(0)->after('cnps');
            $table->decimal('cnps_patronale', 15, 2)->default(0)->after('cnps_salariale');
            
            // ===== RENFORCEMENT =====
            $table->boolean('est_generer_auto')->default(false)->after('statut');
        });
    }

    public function down(): void
    {
        Schema::table('rh_bulletins_paie', function (Blueprint $table) {
            $table->dropForeign(['contrat_id']);
            $table->dropColumn(['contrat_id', 'base_cnps', 'cnps_salariale', 'cnps_patronale', 'est_generer_auto']);
        });
    }
};