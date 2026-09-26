<?php
// database/migrations/xxxx_xx_xx_add_beneficiaire_id_to_affectations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            // Nullable : soit c'est une affectation de dossier (null),
            // soit c'est une affectation de bénéficiaire (id rempli)
            $table->foreignId('beneficiaire_id')
                  ->nullable()
                  ->after('dossier_client_id')
                  ->constrained('beneficiaires')
                  ->cascadeOnDelete();

            $table->index(['dossier_client_id', 'beneficiaire_id']);
        });
    }

    public function down(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            $table->dropForeign(['beneficiaire_id']);
            $table->dropIndex(['dossier_client_id', 'beneficiaire_id']);
            $table->dropColumn('beneficiaire_id');
        });
    }
};