<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajouter la colonne user_reference aux 4 tables de paiement
     */
    public function up(): void
    {
        // ═══════════════════════════════════════════════════════════
        // 1. Paiements techniques
        // ═══════════════════════════════════════════════════════════
        if (!Schema::hasColumn('paiements_techniques', 'user_reference')) {
            Schema::table('paiements_techniques', function (Blueprint $table) {
                $table->string('user_reference', 50)->nullable()->after('note');
                $table->index('user_reference'); // ✅ index pour les filtres
            });
        }

        // ═══════════════════════════════════════════════════════════
        // 2. Paiements dossier (superficie)
        // ═══════════════════════════════════════════════════════════
        if (!Schema::hasColumn('paiements_dossier', 'user_reference')) {
            Schema::table('paiements_dossier', function (Blueprint $table) {
                $table->string('user_reference', 50)->nullable()->after('note');
                $table->index('user_reference');
            });
        }

        // ═══════════════════════════════════════════════════════════
        // 3. Paiements logistiques
        // ═══════════════════════════════════════════════════════════
        if (!Schema::hasColumn('paiements_logistiques', 'user_reference')) {
            Schema::table('paiements_logistiques', function (Blueprint $table) {
                $table->string('user_reference', 50)->nullable()->after('note');
                $table->index('user_reference');
            });
        }

        // ═══════════════════════════════════════════════════════════
        // 4. Paiements morcellements
        // ═══════════════════════════════════════════════════════════
        if (!Schema::hasColumn('paiements_morcellements', 'user_reference')) {
            Schema::table('paiements_morcellements', function (Blueprint $table) {
                $table->string('user_reference', 50)->nullable()->after('note');
                $table->index('user_reference');
            });
        }
    }

    /**
     * Annuler la migration (rollback)
     */
    public function down(): void
    {
        if (Schema::hasColumn('paiements_techniques', 'user_reference')) {
            Schema::table('paiements_techniques', function (Blueprint $table) {
                $table->dropIndex(['user_reference']);
                $table->dropColumn('user_reference');
            });
        }

        if (Schema::hasColumn('paiements_dossier', 'user_reference')) {
            Schema::table('paiements_dossier', function (Blueprint $table) {
                $table->dropIndex(['user_reference']);
                $table->dropColumn('user_reference');
            });
        }

        if (Schema::hasColumn('paiements_logistiques', 'user_reference')) {
            Schema::table('paiements_logistiques', function (Blueprint $table) {
                $table->dropIndex(['user_reference']);
                $table->dropColumn('user_reference');
            });
        }

        if (Schema::hasColumn('paiements_morcellements', 'user_reference')) {
            Schema::table('paiements_morcellements', function (Blueprint $table) {
                $table->dropIndex(['user_reference']);
                $table->dropColumn('user_reference');
            });
        }
    }
};