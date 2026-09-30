<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historique_affectations', function (Blueprint $table) {
            // ✅ Ajoute la colonne si elle n'existe pas
            if (!Schema::hasColumn('historique_affectations', 'beneficiaire_id')) {
                $table->unsignedBigInteger('beneficiaire_id')
                      ->nullable()
                      ->after('dossier_client_id');

                // Index pour la performance
                $table->index('beneficiaire_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('historique_affectations', function (Blueprint $table) {
            if (Schema::hasColumn('historique_affectations', 'beneficiaire_id')) {
                $table->dropIndex(['beneficiaire_id']);
                $table->dropColumn('beneficiaire_id');
            }
        });
    }
};