<?php
// database/migrations/xxxx_xx_xx_add_etapes_to_beneficiaires_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beneficiaires', function (Blueprint $table) {
            // Étapes (mêmes champs que dossier principal)
            $table->date('implantation_prevue')->nullable()->after('notes');
            $table->date('deja_implante')->nullable()->after('implantation_prevue');
            $table->date('dossier_technique')->nullable()->after('deja_implante');
            $table->date('morcellement')->nullable()->after('dossier_technique');
            $table->string('etape_actuelle', 40)->nullable()->after('morcellement');
        });
    }

    public function down(): void
    {
        Schema::table('beneficiaires', function (Blueprint $table) {
            $table->dropColumn([
                'implantation_prevue',
                'deja_implante',
                'dossier_technique',
                'morcellement',
                'etape_actuelle',
            ]);
        });
    }
};